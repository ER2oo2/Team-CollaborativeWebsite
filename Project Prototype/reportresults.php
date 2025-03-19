<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) { 
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
// Retrieve report
$report_results = $_SESSION['reportResults'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Results</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // JavaScript function to print the results
        function printResults() {
            window.print();
        }

        // JavaScript function to email all results
        function emailAllResults() {
            alert("Email sent to all results.");
        }
    </script>
</head>
<body>
    <!-- Header and navigation (unchanged) -->
    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database</span>
    </header>
    
    <?php include 'navbar.php'; ?>

    <main>
        <div class="report-results-container">
            <h2>Report Results</h2>

            <!-- Report Summary -->
            <div class="report-summary">
                <p><strong>Report Parameters:</strong></p>
                <ul>
                    <li><strong>Date Range:</strong> <?php echo htmlspecialchars($_SESSION['reportParams']['date_range']); ?></li>
                    <li><strong>Certification Status:</strong> <?php echo htmlspecialchars($_SESSION['reportParams']['cert_status']); ?></li>
                    <li><strong>Aid Type:</strong> <?php echo htmlspecialchars($_SESSION['reportParams']['aid_type']); ?></li>
                </ul>
            </div>

            <!-- Report Results Table -->
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Certification Status</th>
                        <th>Aid Type</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_results as $result) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['stu_id']); ?></td>
                            <td><?php echo htmlspecialchars($result['stu_lname']); ?></td>
                            <td><?php echo htmlspecialchars($result['stu_fname']); ?></td>
                            <td><?php echo htmlspecialchars($result['stu_email']); ?></td>
                            <td><?php echo ($result['cert_status'] == 1) ? 'Certified' : 'Not Certified'; ?></td>
                            <td><?php echo htmlspecialchars($result['stu_aid_type']); ?></td>
                            <td><?php echo htmlspecialchars($result['stu_aid_bal_months'] . ' Months, ' . $result['stu_aid_bal_days'] . ' Days'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Action Buttons for Results -->
            <div class="results-actions">
                <button onclick="emailAllResults()" class="option-button">Email All Results</button>
                <button onclick="printResults()" class="option-button">Print Results</button>
            </div>
        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>
</body>
</html>
