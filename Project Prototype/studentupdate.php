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


// Fetch student details
$query = 'SELECT * FROM student WHERE stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();

// Fetch certification details
$query = 'SELECT * FROM certification WHERE stu_id = :student_id ORDER BY cert_date DESC LIMIT 1';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$certification = $statement->fetch();
$statement->closeCursor();

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update aid balance, certification status, and certification date
    $months = filter_input(INPUT_POST, 'stu_aid_bal_months', FILTER_SANITIZE_NUMBER_INT);
    $days = filter_input(INPUT_POST, 'stu_aid_bal_days', FILTER_SANITIZE_NUMBER_INT);
    $cert_status = filter_input(INPUT_POST, 'cert_status', FILTER_SANITIZE_NUMBER_INT);
    $cert_date = filter_input(INPUT_POST, 'cert_date');
    // Set to null if blank
    if (empty($cert_date)) {
        $cert_date = null;
    }

    // Update the student table (aid balance)
    $query1 = 'UPDATE student SET stu_aid_bal_months = :months, stu_aid_bal_days = :days WHERE stu_id = :stu_id';
    $statement1 = $db->prepare($query1);
    $statement1->bindParam(':months', $months);
    $statement1->bindParam(':days', $days);
    $statement1->bindParam(':stu_id', $student_id);
    $statement1->execute();
    $statement1->closeCursor();

    // Update the certification table (cert_status and cert_date)
    $query2 = 'UPDATE certification SET cert_status = :cert_status, cert_date = :cert_date WHERE stu_id = :stu_id';
    $statement2 = $db->prepare($query2);
    $statement2->bindParam(':cert_status', $cert_status);
    if ($cert_date === null) {
        $statement2->bindValue(':cert_date', null, PDO::PARAM_NULL);
    } else {
        $statement2->bindParam(':cert_date', $cert_date);
    }
    $statement2->bindParam(':stu_id', $student_id);
    $statement2->execute();
    $statement2->closeCursor();

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
        <form method="post" action="">
            <div class="student-details">
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['stu_id']); ?></p>
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($student['stu_fname']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($student['stu_lname']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($student['stu_address']); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($student['stu_city']); ?></p>
                <p><strong>State:</strong> <?php echo htmlspecialchars($student['stu_state']); ?></p>
                <p><strong>Zip Code:</strong> <?php echo htmlspecialchars($student['stu_zip']); ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($student['stu_phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['stu_email']); ?></p>

                <!-- Editable Aid Balance Section -->
                <p><strong>Aid Balance:</strong></p>
                <p>
                    Months: <input type="number" name="stu_aid_bal_months" value="<?php echo htmlspecialchars($student['stu_aid_bal_months']); ?>" required>
                    Days: <input type="number" name="stu_aid_bal_days" value="<?php echo htmlspecialchars($student['stu_aid_bal_days']); ?>" required>
                </p>

                <!-- Editable Certification Status Section -->
                <p><strong>Certification Status:</strong></p>
                <p>
                    <select name="cert_status">
                        <option value="1" <?php echo ($certification && $certification['cert_status'] == 1) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo ($certification && $certification['cert_status'] == 0) ? 'selected' : ''; ?>>No</option>
                    </select>
                </p>

                <!-- Editable Certification Date Section -->
                <p><strong>Certification Date:</strong></p>
                <p>
                    <input type="date" name="cert_date" value="<?php echo htmlspecialchars($certification['cert_date']); ?>">
                </p>

                <!-- Combined Update Button -->
                <p>
                    <button type="submit" name="update_all" class="email-button">Update All</button>
                </p>
            </div>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
