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

// Get form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $cert_status = $_POST['cert-status'];
    $aid_type = $_POST['aid-type'];


// Save report parameters to session
    $_SESSION['reportParams'] = [
        'date_range' => "$start_date to $end_date",
        'cert_status' => $cert_status,
        'aid_type' => $aid_type
    ];
    // SQL Query
    $query = 'SELECT student.stu_id, student.stu_fname, student.stu_lname, student.stu_email,
                     certification.cert_status, student.stu_aid_bal_months, student.stu_aid_bal_days
              FROM student
              LEFT JOIN certification ON student.stu_id = certification.stu_id
              WHERE 1=1';

    // Apply filters
    if (!empty($start_date) && !empty($end_date)) {
        $query .= " AND certification.cert_date BETWEEN :start_date AND :end_date";
    }
    if ($cert_status === 'certified') {
        $query .= " AND certification.cert_status = 1";
    } elseif ($cert_status === 'not-certified') {
        $query .= " AND (certification.cert_status = 0 OR certification.cert_status IS NULL)";
    }
    if ($aid_type !== 'all') {
        $query .= " AND student.stu_aid_type = :aid_type";
    }

    // Execute Query
    $statement = $db->prepare($query);

    if (!empty($start_date) && !empty($end_date)) {
        $statement->bindParam(':start_date', $start_date);
        $statement->bindParam(':end_date', $end_date);
    }
    if ($aid_type !== 'all') {
        $statement->bindParam(':aid_type', $aid_type);
    }

    $statement->execute();
    $report_results = $statement->fetchAll();
    $statement->closeCursor();

    // Save results to session
    $_SESSION['reportResults'] = $report_results;

    // Redirect
    header('Location: reportresults.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // JavaScript function to print the report
        function printReport() {
        window.print();
        }

        // JavaScript function to email all returned results
        function emailAllResults() {
        // Normally this would send an email, but for demonstration purposes, it's an alert.
        alert("Email sent to all returned results.");
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
        <div class="report-parameters-container">
            <h2>Report Parameters</h2>
            <form action="#" method="post" class="report-parameters-form">

                <!-- Date Range -->
                <div class="form-group">
                    <label for="start-date">Start Date:</label>
                    <input type="date" id="start-date" name="start-date">
                </div>

                <div class="form-group">
                    <label for="end-date">End Date:</label>
                    <input type="date" id="end-date" name="end-date">
                </div>

                <!-- Certification Status -->
                <div class="form-group">
                    <label for="cert-status">Certification Status:</label>
                    <select id="cert-status" name="cert-status">
                        <option value="all">All</option>
                        <option value="certified">Certified</option>
                        <option value="not-certified">Not Certified</option>
                    </select>
                </div>

                <!-- Aid Type -->
                <div class="form-group">
                    <label for="aid-type">Aid Type:</label>
                    <select id="aid-type" name="aid-type">
                        <option value="all">All</option>
                        <option value="911">Post-9/11 GI Bill</option>
                        <option value="montgomery">Montgomery GI Bill</option>
                        <option value="va_scholarship">VA Scholorship</option>

                    </select>
                </div>

                <!-- Generate Report Button -->
                <button type="submit" class="option-button">Generate Report</button>
            </form>

            <!-- Action Buttons for Report -->
            <div class="report-actions">
                <button onclick="emailAllResults()" class="option-button">Email All Results</button>
                <button onclick="printReport()" class="option-button">Print Report</button>
            </div>
        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>

</body>
</html>
