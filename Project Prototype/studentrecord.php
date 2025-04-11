<?php
date_default_timezone_set('America/New_York');
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
if ($selectedStudents) {
    $_SESSION['selected_students'] = $selectedStudents;
}
// Retrieve the selected student IDs from the form POST data
$student_id = isset($_GET['stu_id']) ? $_GET['stu_id'] : (isset($_POST['select-student']) ? $_POST['select-student'][0] : null);

if (!$student_id) {
    echo "No student ID provided.";
    exit();
}

// Debug output
//echo "Student ID passed to this page: " . htmlspecialchars($student_id);

// Modified query to join with the benefit table
$query = 'SELECT s.*, b.benefit_type AS stu_benefit_type
          FROM student s
          LEFT JOIN benefit b ON s.benefit_type_id = b.benefit_type_id
          WHERE s.stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();


// Query for all certification dates for the student
$queryCertDates = 'SELECT cert_date FROM certification WHERE stu_id = :student_id ORDER BY cert_date DESC';
$statementCertDates = $db->prepare($queryCertDates);
$statementCertDates->bindParam(':student_id', $student_id);
$statementCertDates->execute();
$certificationDates = $statementCertDates->fetchAll(PDO::FETCH_COLUMN);
$statementCertDates->closeCursor();

// Modified query to join with the staff table
$queryEmails = 'SELECT ets.date_sent, et.tmplt_subject, s.staff_username
                  FROM email_to_student ets
                  JOIN email_template et ON ets.tmplt_id = et.tmplt_id
                  JOIN staff s ON ets.staff_id = s.staff_id
                  WHERE ets.stu_id = :student_id
                  ORDER BY ets.email_id DESC';
$statementEmails = $db->prepare($queryEmails);
$statementEmails->bindParam(':student_id', $student_id);
$statementEmails->execute();
$emails = $statementEmails->fetchAll(PDO::FETCH_ASSOC);
$statementEmails->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Student Record</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database</span>
    </header>

    <?php include 'navbar.php'; ?>
    <h1 style="text-align: center; color:black;">Student Record</h1>
    <main>
        <!-- Display the details of teh student -->
        <div class="student-record-container">

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
                <p><strong>Benefit Type:</strong> <?php echo htmlspecialchars($student['stu_benefit_type']); ?></p>
                <p><strong>Certification Status:</strong>
                    <?php //display certification status and date if available
                    if ($certificationDates) {
                        echo htmlspecialchars($certificationDates[0] ? 'Certified (as of ' . date('m-d-Y', strtotime($certificationDates[0])) . ')' : 'Not Certified');
                    } else {
                        echo 'No Certifications';
                    }
                    ?>
                </p>
                <p><strong>Benefit Balance:</strong></p>
                <p>
                    <!-- Display Remaining Benefit Balance -->
                    Months: <?php echo htmlspecialchars($student['stu_aid_bal_months']); ?>
                    Days: <?php echo htmlspecialchars($student['stu_aid_bal_days']); ?>
                </p>
                </p>
                
                <!-- Date(s) the student was certified -->
                <p><strong>Certification Dates:</strong></p>
                <?php if (!empty($certificationDates)): ?>
                    <select id="cDropdown">
                        <?php foreach ($certificationDates as $date): ?>
                            <?php if ($date !== null): ?>
                                <option><?php echo htmlspecialchars(date('m-d-Y', strtotime($date))); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p>No Certifications available for this student.</p>
                <?php endif; ?>
                
                <!-- List of recorded emails that were sent to the student -->
                <p><strong>Emails Sent to Student:</strong></p>
                <?php if (!empty($emails)): ?>
                    <select id="emailDropdown">
                        <?php foreach ($emails as $email): ?>
                            <option>
                                <?php
                                $dateTime = new DateTime($email['date_sent'], new DateTimeZone('UTC'));
                                // Convert the time to Eastern Time:
                                $dateTime->setTimezone(new DateTimeZone('America/New_York'));
                                echo htmlspecialchars('Subject: ' . $email['tmplt_subject'] . ' | Sent: ' . $dateTime->format('m-d-Y h:i:s A') . ' | Sent by: ' . $email['staff_username']);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p>No emails have been sent to this student.</p>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <?php if ($student): ?>
                <form action="email.php" method="post">
                    <input type="hidden" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>">
                    <button type="submit" class="email-button">Email Student(s)</button>
                </form>
            <?php endif; ?>
            <button class="email-button" onclick="location.href='studentupdate.php?stu_id=<?php echo htmlspecialchars($student['stu_id']); ?>'">Update Student</button>

        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>

    <script>//Handle students with a Benefit type of "other"
        document.getElementById("benefitType").addEventListener("change", function() {
            if (this.value === "other") {
                document.getElementById("newBenefitDiv").style.display = "block";
            } else {
                document.getElementById("newBenefitDiv").style.display = "none";
            }
        });
    </script>
</body>

</html>