<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure a staff member is logged in and is an admin
if (!isset($_SESSION['staff'])) {
    header('Location: login.php');
    exit();
}

$message = ''; // Variable to store messages for the user

// Handle Reset All Certifications
if (isset($_POST['reset_all_certifications'])) {
    $query = "UPDATE certification SET cert_status = 0";
    $statement = $db->prepare($query);
    if ($statement->execute()) {
        $message = "<p style='color: green;'>All certification statuses have been reset to No.</p>";
    } else {
        $message = "<p style='color: red;'>Error resetting certifications: " . print_r($statement->errorInfo(), true) . "</p>";
    }
    $statement->closeCursor();
}

// Handle Delete Student
if (isset($_POST['delete_student']) && isset($_POST['stu_id'])) {
    $stu_id = $_POST['stu_id'];
    $deleteQuery = "DELETE FROM student WHERE stu_id = :stu_id";
    $deleteStatement = $db->prepare($deleteQuery);
    $deleteStatement->bindParam(':stu_id', $stu_id);
    if ($deleteStatement->execute()) {
        $message .= "<p style='color: green;'>Student with ID " . htmlspecialchars($stu_id) . " has been deleted.</p>";
    } else {
        $message .= "<p style='color: red;'>Error deleting student with ID " . htmlspecialchars($stu_id) . ": " . print_r($deleteStatement->errorInfo(), true) . "</p>";
    }
    $deleteStatement->closeCursor();
}

// Default sort by last name
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'stu_lname'; // Default to 'stu_lname'
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] == 'desc' ? 'desc' : 'asc'; // Default to 'asc'

// Get the search query (if any)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the query to include last email sent date and handle search
$query = "
    SELECT s.stu_id, s.stu_fname, s.stu_lname, b.benefit_type, c.cert_status,
           MAX(ets.date_sent) AS last_email_sent
    FROM student s
    LEFT JOIN benefit b ON s.benefit_type_id = b.benefit_type_id
    LEFT JOIN certification c ON s.stu_id = c.stu_id
    LEFT JOIN email_to_student ets ON s.stu_id = ets.stu_id
    WHERE s.stu_fname LIKE :search_query OR s.stu_lname LIKE :search_query
    GROUP BY s.stu_id, s.stu_fname, s.stu_lname, b.benefit_type, c.cert_status
    ORDER BY $sort_by $sort_order
";
$statement = $db->prepare($query);
$statement->bindValue(':search_query', "%$search_query%", PDO::PARAM_STR);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Admin Page</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Custom styles for stacking elements */
        .stacked-container {
            display: flex;
            flex-direction: column;
            align-items: center; /* Center items horizontally within the container */
            margin-bottom: 20px;
        }

        .stacked-container>* {
            margin-bottom: 15px;
            width: 95%; /* Ensure elements within the stacked container don't stretch too wide */
        }

        .stacked-container h2 {
            text-align: center; /* Center the H2 text */
            width: 100%; /* Make sure the H2 takes the full width to be centered */
            margin-bottom: 20px; /* Add some space below the title */
        }

        table {
            width: 95%;
            /* Adjust width as needed */
            margin: 20px auto;
            /* Center the table */
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            /* Optional: Add a subtle shadow */
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            /* Increase padding for better spacing */
            text-align: left;
            vertical-align: middle;
            /* Vertically align content in cells */
        }

        th {
            background-color: #001f3f;
            /* Use header background color from styles.css */
            color: white;
            font-weight: bold;
            text-align: center;
            /* Center the header text */
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
            /* Optional: Add alternating row colors */
        }

        td:nth-child(1) {
            /* Style for Student ID column (optional) */
            text-align: center;
        }

        td:nth-child(4) {
            /* Style for Certification Status column (optional) */
            text-align: center;
        }

        td:nth-child(7) {
            /* Style for View Record button column */
            text-align: center;
        }

        td:nth-child(8) {
            /* Style for Delete button column */
            text-align: center;
        }

        .small-button,
        .view-record-btn {
            display: inline-block;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #dc3545;
            transition: background-color 0.3s;
        }

        .small-button:hover,
        .view-record-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database - Admin</span>
    </header>

    <?php include 'navbar.php'; ?>

    <div class="admin-header">
                <h1 style="color: black">Admin Page</h1>

    </div>

    <div>
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>
    </div>

    <main>
        
        <div class="stacked-container">

            <div>
                <form method="post" onsubmit="return confirm('Are you sure you want to reset all certification statuses to No?');">
                    <button type="submit" class="option-button" name="reset_all_certifications">Reset All Certifications to Not Certified</button>
                </form>
            </div>



            <form method="get" class="stacked-container">
                <label for="search">Search by Student Last Name:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Enter student last name">
                <button type="submit" class="option-button">Search</button>
            </form>

            <form method="get" class="stacked-container">
                <label for="sort_by">Sort By:</label>
                <select name="sort_by" id="sort_by">
                    <option value="stu_lname" <?php echo $sort_by == 'stu_lname' ? 'selected' : ''; ?>>Last Name</option>
                    <option value="benefit_type" <?php echo $sort_by == 'benefit_type' ? 'selected' : ''; ?>>Benefit Type</option>
                    <option value="last_email_sent" <?php echo $sort_by == 'last_email_sent' ? 'selected' : ''; ?>>Last Email Sent</option>
                </select>
                <label for="sort_order">Order:</label>
                <select name="sort_order" id="sort_order">
                    <option value="asc" <?php echo $sort_order == 'asc' ? 'selected' : ''; ?>>Ascending</option>
                    <option value="desc" <?php echo $sort_order == 'desc' ? 'selected' : ''; ?>>Descending</option>
                </select>
                <button type="submit" class="option-button">Sort</button>
            </form>
        </div>

        <div class="stacked-container">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Certification Status</th>
                        <th>Benefit Type</th>
                        <th>Last Email Sent</th>
                        <th>View Student Record</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9">No students found.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['stu_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['stu_fname']); ?></td>
                                    <td><?php echo htmlspecialchars($student['stu_lname']); ?></td>
                                    <td><?php echo htmlspecialchars($student['cert_status'] === '1' ? 'Yes' : ($student['cert_status'] === '0' ? 'No' : 'N/A')); ?></td>
                                    <td><?php echo htmlspecialchars($student['benefit_type'] ?: 'N/A'); ?></td>
                                    <td><?php echo $student['last_email_sent'] ? date('m-d-Y', strtotime($student['last_email_sent'])) : 'N/A'; ?></td>
                                    <td style="text-align: center;">
                                        <a href="studentrecord.php?stu_id=<?php echo htmlspecialchars($student['stu_id']); ?>" class="small-button delete-btn">View Record</a>
                                    </td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete student <?php echo htmlspecialchars($student['stu_fname'] . ' ' . $student['stu_lname']); ?>?');">
                                            <input type="hidden" name="stu_id" value="<?php echo htmlspecialchars($student['stu_id']); ?>">
                                            <button type="submit" class="small-button delete-btn" name="delete_student">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>

            <footer>
                Pennsylvania Western University
            </footer>
        </body>

        </html>