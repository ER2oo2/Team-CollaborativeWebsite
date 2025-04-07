<?php
require_once('dbconnect.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for logged-in staff
if (!isset($_SESSION['staff'])) {
    echo "No user is logged in";
    header('Location: login.php');
    exit();
}

// Retrieve search results from the session
if (isset($_SESSION['searchResults'])) {
    // Filter the search results to exclude those with INACTIVE benefit_type
    $students = array_filter($_SESSION['searchResults'], function ($student) use ($db) {
        if (isset($student['benefit_type_id'])) {
            $benefitTypeId = $student['benefit_type_id'];
            $query = 'SELECT benefit_type FROM benefit WHERE benefit_type_id = :benefit_type_id';
            $statement = $db->prepare($query);
            $statement->bindParam(':benefit_type_id', $benefitTypeId);
            $statement->execute();
            $benefit = $statement->fetch(PDO::FETCH_ASSOC);
            $statement->closeCursor();

            if ($benefit && strtoupper($benefit['benefit_type']) === 'INACTIVE') {
                return false; // Exclude the student
            }
        }
        return true; // Include the student if benefit_type is not INACTIVE or benefit_type_id is not set
    });
} else {
    echo "No search results found. Please try your search again.";
    exit();
}

// Store selected students in session if they are selected.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select-student'])) {
    $_SESSION['selected_students'] = $_POST['select-student'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Search Results</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('input[name="select-student[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
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
    <div class="results-container">
        <h2>Search Results</h2>
        <form action="studentrecord.php" method="post">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Certified</th>
                        <th>Benefit Months</th>
                        <th>Benefit Days</th>
                        <th>
                            Select All<br>
                            <input type="checkbox" onclick="toggleSelectAll(this)">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="7">No active students found based on your search criteria.</td></tr>
                    <?php else: ?>
                        <?php
                        // Track displayed student IDs to avoid duplicates
                        $displayedStudents = [];

                        foreach ($students as $student) :
                            $student_id = $student['stu_id'];

                            // Avoid showing duplicates
                            if (in_array($student_id, $displayedStudents)) {
                                continue;
                            }
                            $displayedStudents[] = $student_id;

                            // Fetch the most recent certification for the current student
                            $query = 'SELECT cert_status FROM certification WHERE stu_id = :student_id ORDER BY cert_date DESC LIMIT 1';
                            $statement = $db->prepare($query);
                            $statement->bindParam(':student_id', $student_id);
                            $statement->execute();
                            $certification = $statement->fetch(PDO::FETCH_ASSOC);
                            $statement->closeCursor();

                            // Determine certification status
                            $cert_status = 'N'; // Default to 'N'
                            if ($certification && $certification['cert_status'] == 1) {
                                $cert_status = 'Y';
                            }
                        ?>
                            <tr>
                                <td>
                                    <a href="studentrecord.php?stu_id=<?php echo htmlspecialchars($student['stu_id']); ?>">
                                        <?php echo htmlspecialchars($student['stu_id']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($student['stu_lname']); ?></td>
                                <td><?php echo htmlspecialchars($student['stu_fname']); ?></td>
                                <td><?php echo $cert_status; ?></td>
                                <td><?php echo htmlspecialchars($student['stu_aid_bal_months']); ?></td>
                                <td><?php echo htmlspecialchars($student['stu_aid_bal_days']); ?></td>
                                <td>
                                    <input type="checkbox" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 20px;">
                <button type="submit" class="option-button">View Student Record</button>
                <button type="submit" formaction="email.php" class="option-button">Email Student(s)</button>
                <button type="submit" formaction="search.php" class="option-button">New Search</button>
            </div>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
