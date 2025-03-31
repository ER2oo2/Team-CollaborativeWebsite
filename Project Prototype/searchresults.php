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
    $students = $_SESSION['searchResults'];
} else {
    echo "No search results found. Please try your search again.";
    exit();
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
                        <th>Aid Months</th>
                        <th>Aid Days</th>
                        <th>
                            Select All<br>
                            <input type="checkbox" onclick="toggleSelectAll(this)"> 
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) : ?>
                        <?php
                        // Fetch certification details for the current student
                        $student_id = $student['stu_id'];
                        $query = 'SELECT * FROM certification WHERE stu_id = :student_id';
                        $statement = $db->prepare($query);
                        $statement->bindParam(':student_id', $student_id);
                        $statement->execute();
                        $certification = $statement->fetchAll();
                        $statement->closeCursor();

                        // Determine certification status
                        $cert_status = 'N'; // Default to 'N'
                        if (!empty($certification)) {
                            foreach ($certification as $cert) {
                                if ($cert['cert_status'] == 1) {
                                    $cert_status = 'Y';
                                    break;
                                }
                            }
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
                </tbody>
            </table>
            <div style="margin-top: 20px;">
                <!-- Normal submission button -->
                <button type="submit" class="option-button">View Student Record</button>
                <!-- Button for emailing. Note the formaction attribute here. -->
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