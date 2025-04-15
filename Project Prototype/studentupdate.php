<?php
require_once('dbconnect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['staff'])) {
    $staff_id = $_SESSION['staff']['staff_username'];
    $staff_fname = $_SESSION['staff']['staff_fname'];
    $staff_lname = $_SESSION['staff']['staff_lname'];
    $staff_email = $_SESSION['staff']['staff_email'];
    $staff_role = $_SESSION['staff']['staff_role'];
} else {
    echo "No user is logged in";
    header('Location: login.php');
    exit();
}

// Retrieve the student ID
$student_id = isset($_GET['stu_id']) ? $_GET['stu_id'] : null;

if (!$student_id) {
    echo "No student ID provided.";
    exit();
}

// Fetch student details with benefit type
$query = 'SELECT s.*, b.benefit_type AS benefit_type_name
          FROM student s
          LEFT JOIN benefit b ON s.benefit_type_id = b.benefit_type_id
          WHERE s.stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();

// Fetch certification details
$queryCert = 'SELECT * FROM certification WHERE stu_id = :student_id ORDER BY cert_date DESC LIMIT 1';
$statementCert = $db->prepare($queryCert);
$statementCert->bindParam(':student_id', $student_id);
$statementCert->execute();
$certification = $statementCert->fetch();
$statementCert->closeCursor();

// Fetch benefit types for the dropdown
$queryBenefitTypes = 'SELECT benefit_type_id, benefit_type FROM benefit ORDER BY benefit_type';
$statementBenefitTypes = $db->prepare($queryBenefitTypes);
$statementBenefitTypes->execute();
$benefitTypes = $statementBenefitTypes->fetchAll(PDO::FETCH_ASSOC);
$statementBenefitTypes->closeCursor();

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    // Sanitize and retrieve student input values... (as in the previous correct versions)
    $stu_fname = filter_input(INPUT_POST, 'stu_fname', FILTER_SANITIZE_STRING);
    $stu_lname = filter_input(INPUT_POST, 'stu_lname', FILTER_SANITIZE_STRING);
    $stu_address = filter_input(INPUT_POST, 'stu_address', FILTER_SANITIZE_STRING);
    $stu_city = filter_input(INPUT_POST, 'stu_city', FILTER_SANITIZE_STRING);
    $stu_state = filter_input(INPUT_POST, 'stu_state', FILTER_SANITIZE_STRING);
    $stu_zip = filter_input(INPUT_POST, 'stu_zip', FILTER_SANITIZE_STRING);
    $stu_phone = filter_input(INPUT_POST, 'stu_phone', FILTER_SANITIZE_STRING);
    $stu_email = filter_input(INPUT_POST, 'stu_email', FILTER_SANITIZE_EMAIL);
    $stu_aid_bal_months = filter_input(INPUT_POST, 'stu_aid_bal_months', FILTER_SANITIZE_NUMBER_INT);
    $stu_aid_bal_days = filter_input(INPUT_POST, 'stu_aid_bal_days', FILTER_SANITIZE_NUMBER_INT);
    $benefit_type_id = $_POST['benefit_type_id'];
    $new_benefit_type = filter_input(INPUT_POST, 'new_benefit_type', FILTER_SANITIZE_STRING);
    $cert_status = filter_input(INPUT_POST, 'cert_status', FILTER_SANITIZE_NUMBER_INT);
    $cert_date = filter_input(INPUT_POST, 'cert_date');

    $final_benefit_type_id = $benefit_type_id;

    // Handle new benefit type... (as in the previous correct version)
    if ($benefit_type_id === 'new' && !empty($new_benefit_type)) {
        // ... (code to check and insert new benefit type) ...
    } elseif (empty($benefit_type_id)) {
        $final_benefit_type_id = null;
    }

    // Update student data (benefit balance and benefit type)
    $queryUpdateStudent = 'UPDATE student SET
                            stu_fname = :stu_fname,
                            stu_lname = :stu_lname,
                            stu_address = :stu_address,
                            stu_city = :stu_city,
                            stu_state = :stu_state,
                            stu_zip = :stu_zip,
                            stu_phone = :stu_phone,
                            stu_email = :stu_email,
                            stu_aid_bal_months = :stu_aid_bal_months,
                            stu_aid_bal_days = :stu_aid_bal_days,
                            benefit_type_id = :benefit_type_id
                          WHERE stu_id = :stu_id';
    $stmtUpdateStudent = $db->prepare($queryUpdateStudent);
    $stmtUpdateStudent->bindParam(':stu_fname', $stu_fname);
    $stmtUpdateStudent->bindParam(':stu_lname', $stu_lname);
    $stmtUpdateStudent->bindParam(':stu_address', $stu_address);
    $stmtUpdateStudent->bindParam(':stu_city', $stu_city);
    $stmtUpdateStudent->bindParam(':stu_state', $stu_state);
    $stmtUpdateStudent->bindParam(':stu_zip', $stu_zip);
    $stmtUpdateStudent->bindParam(':stu_phone', $stu_phone);
    $stmtUpdateStudent->bindParam(':stu_email', $stu_email);
    $stmtUpdateStudent->bindParam(':stu_aid_bal_months', $stu_aid_bal_months, PDO::PARAM_INT);
    $stmtUpdateStudent->bindParam(':stu_aid_bal_days', $stu_aid_bal_days, PDO::PARAM_INT);
    $stmtUpdateStudent->bindParam(':benefit_type_id', $final_benefit_type_id, PDO::PARAM_INT);
    $stmtUpdateStudent->bindParam(':stu_id', $student_id);
    if ($stmtUpdateStudent->execute()) {
        $updateMessage = "<p style='color: green;'>Student information updated successfully.</p>";
    } else {
        $updateMessage = "<p style='color: red;'>Error updating student information: " . print_r($stmtUpdateStudent->errorInfo(), true) . "</p>";
    }
    $stmtUpdateStudent->closeCursor();

    // Insert a *new* certification record if a certification date is provided
    if (!empty($cert_date)) {
        $queryInsertCert = 'INSERT INTO certification (stu_id, cert_date, cert_status) VALUES (:stu_id, :cert_date, :cert_status)';
        $stmtInsertCert = $db->prepare($queryInsertCert);
        $stmtInsertCert->bindParam(':stu_id', $student_id);
        $stmtInsertCert->bindParam(':cert_date', $cert_date);
        $stmtInsertCert->bindParam(':cert_status', $cert_status, PDO::PARAM_INT);
        if ($stmtInsertCert->execute()) {
            $updateMessage .= "<p style='color: green;'>New certification added successfully.</p>";
        } else {
            $updateMessage .= "<p style='color: red;'>Error adding new certification: " . print_r($stmtInsertCert->errorInfo(), true) . "</p>";
        }
        $stmtInsertCert->closeCursor();
    }

    // Redirect back to studentRecord.php
    header('Location: studentrecord.php?stu_id=' . $student_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Update Student Record</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        #newBenefitDiv {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<header>
    <img src="PennWestLogo.png" alt="PennWest University Logo">
    <span>PennWest Financial Aid Veteran’s Database</span>
</header>

<?php include 'navbar.php'; ?>

<main>
    <div class="student-record-container">
        <h2>Update Student Record</h2>
        <?php if (isset($updateMessage)) echo $updateMessage; ?>
        <form method="post" action="">
            <div class="student-details">
                <div class="form-group">
                    <label for="stu_id">Student ID:</label>
                    <input type="text" id="stu_id" name="stu_id" value="<?php echo htmlspecialchars($student['stu_id']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="stu_fname">First Name:</label>
                    <input type="text" id="stu_fname" name="stu_fname" value="<?php echo htmlspecialchars($student['stu_fname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stu_lname">Last Name:</label>
                    <input type="text" id="stu_lname" name="stu_lname" value="<?php echo htmlspecialchars($student['stu_lname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stu_address">Address:</label>
                    <input type="text" id="stu_address" name="stu_address" value="<?php echo htmlspecialchars($student['stu_address']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_city">City:</label>
                    <input type="text" id="stu_city" name="stu_city" value="<?php echo htmlspecialchars($student['stu_city']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_state">State:</label>
                    <input type="text" id="stu_state" name="stu_state" value="<?php echo htmlspecialchars($student['stu_state']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_zip">Zip Code:</label>
                    <input type="text" id="stu_zip" name="stu_zip" value="<?php echo htmlspecialchars($student['stu_zip']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_phone">Phone Number:</label>
                    <input type="text" id="stu_phone" name="stu_phone" value="<?php echo htmlspecialchars($student['stu_phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_email">Email:</label>
                    <input type="email" id="stu_email" name="stu_email" value="<?php echo htmlspecialchars($student['stu_email']); ?>">
                </div>
                <div class="form-group">
                    <label for="stu_aid_bal_months">Benefit Balance (Months):</label>
                    <input type="number" id="stu_aid_bal_months" name="stu_aid_bal_months" value="<?php echo htmlspecialchars($student['stu_aid_bal_months']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="stu_aid_bal_days">Benefit Balance (Days):</label>
                    <input type="number" id="stu_aid_bal_days" name="stu_aid_bal_days" value="<?php echo htmlspecialchars($student['stu_aid_bal_days']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="benefit_type_id">Benefit Type:</label>
                    <select id="benefit_type_id" name="benefit_type_id">
                        <option value="">-- Select Benefit Type --</option>
                        <?php foreach ($benefitTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type['benefit_type_id']); ?>" <?php if ($student['benefit_type_id'] == $type['benefit_type_id']) echo 'selected'; ?>><?php echo htmlspecialchars($type['benefit_type']); ?></option>
                        <?php endforeach; ?>
                        <option value="new">-- Add New Benefit Type --</option>
                    </select>
                    <div id="newBenefitDiv" style="display: none;">
                        <label for="new_benefit_type">New Benefit Type:</label>
                        <input type="text" id="new_benefit_type" name="new_benefit_type">
                    </div>
                </div>
                <div class="form-group">
                    <label for="cert_status">Certification Status:</label>
                    <select name="cert_status" id="cert_status">
                        <option value="1" <?php echo ($certification && $certification['cert_status'] == 1) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo ($certification && $certification['cert_status'] == 0) ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cert_date">Certification Date:</label>
                    <input type="date" id="cert_date" name="cert_date" value="<?php echo htmlspecialchars($certification['cert_date']); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" name="update_student" class="email-button">Update Student</button>
                </div>
            </div>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var benefitTypeDropdown = document.getElementById("benefit_type_id");
        var newBenefitDiv = document.getElementById("newBenefitDiv");

        if (benefitTypeDropdown) {
            benefitTypeDropdown.addEventListener("change", function() {
                if (this.value === "new") {
                    newBenefitDiv.style.display = "block";
                } else {
                    newBenefitDiv.style.display = "none";
                }
            });
        }
    });
</script>

</body>
</html>