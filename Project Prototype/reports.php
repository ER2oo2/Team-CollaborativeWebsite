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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $cert_status = $_POST['cert-status'];
    $aid_balance = $_POST['aid-balance']; // Retrieve aid balance from the form
    $benefit_type = $_POST['benefit-type']; // Retrieve benefit type from the form

    // Save report parameters to session
    $_SESSION['reportParams'] = [
        'date_range' => "$start_date to $end_date",
        'cert_status' => $cert_status,
        'aid_balance' => $aid_balance,
        'benefit_type' => $benefit_type,
    ];

    // SQL Query
    $query = 'SELECT *
              FROM student
              LEFT JOIN certification ON student.stu_id = certification.stu_id
              LEFT JOIN benefit ON student.benefit_type_id = benefit.benefit_type_id
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
    if ($aid_balance === 'more-than-9') {
        $query .= " AND student.stu_aid_bal_months > 9";
    } elseif ($aid_balance === '6-9') {
        $query .= " AND student.stu_aid_bal_months BETWEEN 6 AND 9";
    } elseif ($aid_balance === '3-6') {
        $query .= " AND student.stu_aid_bal_months BETWEEN 3 AND 6";
    } elseif ($aid_balance === '3-or-less') {
        $query .= " AND student.stu_aid_bal_months <= 3";
    }
    if (!empty($benefit_type)) {
        $query .= " AND benefit.benefit_type_id = :benefit_type";
    }

    $query .= " GROUP BY student.stu_id"; // Group by student ID to avoid duplicates

    // Execute Query
    $statement = $db->prepare($query);

    if (!empty($start_date) && !empty($end_date)) {
        $statement->bindParam(':start_date', $start_date);
        $statement->bindParam(':end_date', $end_date);
    }
    if (!empty($benefit_type)) {
        $statement->bindParam(':benefit_type', $benefit_type, PDO::PARAM_INT);
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
    <title>Veteran DB: Reports</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // JavaScript function to print the report
        function printReport() {
            window.print();
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

                <!-- End Date -->
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

                <!-- Aid Balance -->
                <div class="form-group">
                    <label for="aid-balance">Benefit Balance (Months):</label>
                    <select id="aid-balance" name="aid-balance">
                        <option value="all">All</option>
                        <option value="more-than-9">More than 9 months</option>
                        <option value="6-9">6-9 months</option>
                        <option value="3-6">3-6 months</option>
                        <option value="3-or-less">3 months or less</option>
                    </select>
                </div>

                <!-- Benefit Type -->
                <div class="form-group">
                    <label for="benefit-type">Benefit Type:</label>
                    <select id="benefit-type" name="benefit-type">
                        <option value="">All</option>
                        <?php
                        // Fetch all benefit types excluding inactive
                        $query = "SELECT benefit_type_id, benefit_type FROM benefit WHERE UPPER(benefit_type) <> 'INACTIVE' ORDER BY benefit_type";
                        $statement = $db->prepare($query);
                        $statement->execute();
                        $benefitTypes = $statement->fetchAll(PDO::FETCH_ASSOC);
                        $statement->closeCursor();

                        foreach ($benefitTypes as $type) {
                            echo '<option value="' . htmlspecialchars($type['benefit_type_id']) . '">' . htmlspecialchars($type['benefit_type']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Generate Report Button -->
                <button type="submit" class="option-button">Generate Report</button>
            </form>

        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>

</body>

</html>