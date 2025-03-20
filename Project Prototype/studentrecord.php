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
     $error = "No user is logged in";
     echo $error;
     header('Location: login.php');
     exit();	
 }
//var_dump($_POST['stu_id']);

// Retrieve the selected student IDs from the form POST data
$selectedStudents = filter_input(INPUT_POST, 'select-student', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$student_id = isset($_GET['stu_id']) ? $_GET['stu_id'] : (isset($_POST['student-id']) ? $_POST['student-id'] : null);
if (!$student_id) {
    echo "No student ID provided.";
    exit();
}
$query = 'SELECT * FROM student WHERE stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();


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
        <!-- Update Student Button -->
        <button class="email-button" onclick="location.href='studentUpdate.php'">Update Student</button>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
