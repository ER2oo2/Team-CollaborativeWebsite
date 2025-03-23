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


// Retrieve stu_id from GET or POST
$selectedStudents = filter_input(INPUT_POST, 'select-student', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

// Retrieve the selected student IDs from the form POST data
$student_id = isset($_GET['stu_id']) ? $_GET['stu_id'] : (isset($_POST['select-student']) ? $_POST['select-student'][0] : null);

if (!$student_id) {
    echo "No student ID provided.";
    exit();
}

// Debug output
//echo "Student ID passed to this page: " . htmlspecialchars($student_id);

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
                if ($certification && isset($certification['cert_status'])) {
                    echo $certification['cert_status'] == 1 ? 'Certified' : 'Not Certified';
                } else {
                    echo 'N/A';
                }
            ?>
            </p>
            <p><strong>Aid Balance:</strong></p>
                <p>
                    Months: <?php echo htmlspecialchars($student['stu_aid_bal_months']); ?>
                    Days: <?php echo htmlspecialchars($student['stu_aid_bal_days']); ?>
                </p>
            </p>
            <p><strong>Certification Date:</strong> 
            <?php 
            if ($certification && isset($certification['cert_date'])) {
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
        <button class="email-button" onclick="location.href='studentupdate.php?stu_id=<?php echo htmlspecialchars($student['stu_id']); ?>'">Update Student</button>

    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
