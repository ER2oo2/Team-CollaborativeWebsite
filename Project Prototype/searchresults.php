<?php
require_once('dbconnect.php');

//validate if there is a session, if not- start one
if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}
    
//checking to see if SESSION variables passed correctly
if (isset($_SESSION['user_session'])) {
   $staff_id = $_SESSION['staff']['staff_id'];
   $staff_fname = $_SESSION['staff']['staff_fname'];
   $staff_lname = $_SESSION['staff']['staff_lname'];
   $staff_email = $_SESSION['staff']['staff_email'];
   $staff_role = $_SESSION['staff']['staff_role'];
} else {
    $error = "No user is logged in";
    echo $error;	
}

//retrieve search results from session
$student = isset($_SESSION['searchResults']) ? $_SESSION['searchResults'] : [];
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // JavaScript function to toggle all checkboxes
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

<nav>
    <a href="index.html">Home</a>
    <a href="search.php">Search</a>
    <a href="newrecord.html">New Record</a>
    <a href="email.html">Email</a>
    <a href="reports.html">Reports</a>
    &ensp;&ensp;&ensp;&ensp;<a href="login.html">Login</a>
    <a href="#logout">| Logout</a>
</nav>

<main>
    <div class="results-container">
        <h2>Search Results</h2>
        <form action="#" method="post">
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
                    <?php foreach ($student as $student) : ?>
                        <tr>
                            <td><a href="#details"><?php echo htmlspecialchars($student['stu_id']); ?></a></td>
                            <td><a href="#details"><?php echo htmlspecialchars($student['stu_lname']); ?></a></td>
                            <td><a href="#details"><?php echo htmlspecialchars($student['stu_fname']); ?></a></td>
                            <td><a href="#details"><?php echo ($student['cert_status'] == 1) ? 'Y' : 'N'; ?></a></td>
                            <td><input type="checkbox" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Submit Button Below the Table -->
            <button type="submit" class="option-button" style="margin-top: 20px;">Submit</button>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>