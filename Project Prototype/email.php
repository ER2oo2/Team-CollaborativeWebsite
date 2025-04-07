<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure a staff member is logged in
if (!isset($_SESSION['staff'])) {
    header('Location: login.php');
    exit();
}

// Retrieve the student IDs from the form POST data
if (isset($_POST['select-student'])) {
    $selected_students = $_POST['select-student'];
    // Store the selected students in the session (optional, but might be useful for subsequent actions)
    $_SESSION['selected_students'] = $selected_students;
} elseif (isset($_SESSION['selected_students'])) {
    // If no POST data is available, retrieve the previously stored selection.
    $selected_students = $_SESSION['selected_students'];
    // Optionally, clear the session data after retrieving it to ensure it doesn't persist.
    // unset($_SESSION['selected_students']);
} else {
    echo "No students selected for emailing.";
    exit();
}

// Debugging: Output the selected student IDs
// echo "<pre>";
// var_dump($selected_students);
// echo "</pre>";

// Fetch student details
$placeholders = implode(',', array_fill(0, count($selected_students), '?'));
$query = "SELECT * FROM student WHERE stu_id IN ($placeholders)";
$stmt = $db->prepare($query);
$stmt->execute($selected_students);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Fetch email templates
$queryTemplates = "SELECT tmplt_id, tmplt_subject, tmplt_body FROM email_template ORDER BY tmplt_id DESC";
$stmtTemplates = $db->prepare($queryTemplates);
$stmtTemplates->execute();
$templates = $stmtTemplates->fetchAll(PDO::FETCH_ASSOC);
$stmtTemplates->closeCursor();

// If there is saved form data, load it into defaults:
$default_subject   = isset($_SESSION['email_form']['subject']) ? $_SESSION['email_form']['subject'] : '';
$default_body      = isset($_SESSION['email_form']['body']) ? $_SESSION['email_form']['body'] : '';
$default_template  = isset($_SESSION['email_form']['template_id']) ? $_SESSION['email_form']['template_id'] : 'new';


// Process saving a new email template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_template'])) {
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $insertQuery = "INSERT INTO email_template (tmplt_subject, tmplt_body) VALUES (?, ?)";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->execute([$subject, $body]);
    $insertStmt->closeCursor();
    header("Location: email.php"); // Refresh the page to reload template list
    exit();
}

// Process saving the email record to the student (this is separate from sending email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_to_database'])) {
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    // If a template ID is submitted and not "new", use it; otherwise, it will be null.
    $template_id = (isset($_POST['template_id']) && $_POST['template_id'] !== 'new') ? $_POST['template_id'] : null;
    $staff_id = $_SESSION['staff']['staff_id'];
    $dateTime = new DateTime();
    $date_sent = $dateTime->format('Y-m-d H:i:s');

    // Store email record for each selected student
    foreach ($selected_students as $stu_id) {
        $insertEmailQuery = "INSERT INTO email_to_student (stu_id, staff_id, tmplt_id, date_sent) VALUES (?, ?, ?, ?)";
        $stmtEmail = $db->prepare($insertEmailQuery);
        if (!$stmtEmail) {
            $error_message = "Database prepare error (email_to_student): " . print_r($db->errorInfo(), true);
            error_log($error_message);
            echo $error_message;
            exit();
        }
        $result = $stmtEmail->execute([$stu_id, $staff_id, $template_id, $date_sent]);
        if (!$result) {
            $error_message = "Database execute error (email_to_student): " . print_r($stmtEmail->errorInfo(), true);
            error_log($error_message);
            echo $error_message;
            exit();
        }
        $stmtEmail->closeCursor();
    }


    // Save the current form selections in session so they persist
    $_SESSION['email_form'] = [
        'subject'     => $subject,
        'body'        => $body,
        'template_id' => isset($_POST['template_id']) ? $_POST['template_id'] : 'new'
    ];

    header("Location: email.php"); // Refresh page with selections preserved
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Veteran DB: Email Students</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Style for form fields and buttons */
        #subject, #body {
            width: 100%;
            font-size: 16px;
            padding: 10px;
            box-sizing: border-box;
        }
        #body {
            height: 250px;
        }
        /* Shared styling for both buttons and links */
        .option-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            width: 200px;
            height: 50px;
            cursor: pointer;
            box-sizing: border-box;
        }
        .option-button span {
            text-align: center;
            display: block;
            width: 100%;
            line-height: 1.5;
        }
    </style>
    <script>
        // JavaScript for handling template selection
        const templates = <?php echo json_encode($templates); ?>;
        function onTemplateChange() {
            const selectedId = document.getElementById('template_id').value;
            const subjectField = document.getElementById('subject');
            const bodyField = document.getElementById('body');
            const template = templates.find(t => t.tmplt_id == selectedId);
            if (template) {
                subjectField.value = template.tmplt_subject;
                bodyField.value = template.tmplt_body;
            } else {
                // If "new" is selected, do not clear the inputs (or optionally, clear them)
                // subjectField.value = "";
                // bodyField.value = "";
            }
        }
        // JavaScript for handling "Send Email"
        function sendEmail() {
            const subject = encodeURIComponent(document.getElementById("subject").value);
            const body = encodeURIComponent(document.getElementById("body").value);
            const recipients = <?php echo json_encode(implode(",", array_map(fn($s) => $s['stu_email'], $students))); ?>;
            const mailtoLink = "mailto:" + recipients + "?subject=" + subject + "&body=" + body;
            window.location.href = mailtoLink;
        }
    </script>
</head>
<body>
    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database</span>
    </header>
    <?php include 'navbar.php'; ?>
    <main>
        <div class="email-form-container">
            <h2>Email Students</h2>
            <div class="preselected-students">
                <h3>Selected Student(s):</h3>
                <p>
                    <?php
                    echo !empty($students)
                        ? implode(', ', array_map(fn($s) => htmlspecialchars($s['stu_fname'] . ' ' . $s['stu_lname'] . ' (' . $s['stu_email'] . ')'), $students))
                        : "No students selected.";
                    ?>
                </p>
            </div>
            <form id="emailForm" action="email.php" method="post">
                <?php if (!empty($selected_students)): ?>
                    <?php foreach ($selected_students as $stu): ?>
                        <input type="hidden" name="selected_students[]" value="<?php echo htmlspecialchars($stu); ?>">
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="form-group">
                    <label for="template_id">Select Template:</label>
                    <select id="template_id" name="template_id" onchange="onTemplateChange()">
                        <option value="new" <?php if ($default_template == 'new') echo 'selected'; ?>>-- Create New Template --</option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo htmlspecialchars($template['tmplt_id']); ?>" <?php if ($default_template == $template['tmplt_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($template['tmplt_subject']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($default_subject); ?>">
                </div>
                <div class="form-group">
                    <label for="body">Email Body:</label>
                    <textarea id="body" name="body" rows="10" required><?php echo htmlspecialchars($default_body); ?></textarea>
                </div>
                <div class="form-group" style="display: flex; justify-content: center; align-items: center;">
                    <button type="submit" class="option-button" name="save_template"><span>Save as Template</span></button>
                    <button type="submit" class="option-button" name="save_to_database" id="saveToStudentButton"><span>Save to Record(s)</span></button>
                    <button type="button" class="option-button" id="sendEmailButton" onclick="sendEmail()"><span>Send Email</span></button>
                    <a href="batchemail.php" class="option-button"><span>Back to Search</span></a>
                </div>
            </form>
        </div>
    </main>
    <footer>
        Pennsylvania Western University
    </footer>
</body>
</html>