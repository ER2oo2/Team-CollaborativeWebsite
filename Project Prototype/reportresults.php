<?php
// Start session
session_start();

if (!isset($_SESSION['reportResults'])) {
    echo "No report results found.";
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
    <nav>
        <a href="index.html">Home</a>
        <a href="search.html">Search</a>
        <a href="newrecord.html">New Record</a>
        <a href="email.html">Email</a>
        <a href="reports.php">Reports</a>
        &ensp;&ensp;&ensp;&ensp;<a href="login.html">Login</a>
        <a href="#logout">| Logout</a>
    </nav>

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
