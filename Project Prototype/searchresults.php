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

// Query the database for certification details.
$query = 'SELECT * FROM certification WHERE stu_id = :student_id';
$statement = $db->prepare($query);
$statement->bindParam(':student_id', $student_id);
$statement->execute();
$certification = $statement->fetchAll();
$statement->closeCursor();
?>

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
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
                        <th>
                            Select All<br>
                            <input type="checkbox" onclick="toggleSelectAll(this)"> 
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td>
                                <a href="studentrecord.php?stu_id=<?php echo htmlspecialchars($student['stu_id']); ?>">
                                    <?php echo htmlspecialchars($student['stu_id']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($student['stu_lname']); ?></td>
                            <td><?php echo htmlspecialchars($student['stu_fname']); ?></td>
                            <td><?php echo htmlspecialchars($certification['cert_status'] == 1) ? 'Y' : 'N'; ?></td>
                            <td>
                                <input type="checkbox" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="option-button" style="margin-top: 20px;">Submit</button>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
