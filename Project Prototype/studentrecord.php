<?php
// Start session
session_start();
require_once('dbconnect.php');

if (!isset($_SESSION['user_session']) || !isset($_SESSION['staff'])) {
    header('Location: login.php');
    exit();
}

// Check for student ID
if (!isset($_GET['stu_id'])) {
    echo "No student ID provided.";
    exit();
}

$student_id = $_GET['stu_id'];

$query = 'SELECT * FROM student WHERE stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();

if (!$student) {
    echo "Student not found.";
    exit();
}

// Query the database for details
$query = 'SELECT * FROM certification WHERE stu_id = :student_id ORDER BY cert_date DESC LIMIT 1';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$certification = $statement->fetch();
$statement->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Record</title>
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
        <h2>Student Record</h2>
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
            <p><strong>Balance:</strong> <?php echo htmlspecialchars($student['stu_aid_bal_months'] . ' Months, ' . $student['stu_aid_bal_days'] . ' Days'); ?></p>
            <p><strong>Certification Status:</strong> 
                <?php 
                if ($certification && $certification['cert_status'] == 1) {
                    echo 'Certified';
                } else {
                    echo 'Not Certified';
                }
                ?>
            </p>
            <p><strong>Certification Date:</strong> 
                <?php 
                if ($certification && $certification['cert_date']) {
                    echo htmlspecialchars($certification['cert_date']);
                } else {
                    echo 'N/A';
                }
                ?>
            </p>
        </div>
        
        <!-- Email Student Button -->
        <button class="email-button" onclick="location.href='mailto:<?php echo htmlspecialchars($student['stu_email']); ?>'">Email Student</button>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
