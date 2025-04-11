<?php
require_once('dbconnect.php');

//validate if there is a session, if not- start one
if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

//checking to see if SESSION variables passed correctly
if (isset($_SESSION['staff'])) {
    $staff_id = $_SESSION['staff']['staff_id'];
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
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <img src="PennWestLogo.png" alt="PennWest University Logo">
    <span>PennWest Financial Aid Veteran’s Database</span>
</header>

<?php include 'navbar.php'; ?>
  <!-- Display error if it exists -->
  <h1><?php if (!empty($error)): ?>

      <?php echo htmlspecialchars($error); ?>

    <?php endif; ?>
  </h1>
<main>

<!-- Buttons for easy page navigation -->
    <div class="options-container">
        <h2>Select an Action</h2>
        <div class="option-buttons">
            <a href="search.php" class="option-button">Search</a>
            <a href="newrecord.php" class="option-button">Add New Record</a>
            <a href="reports.php" class="option-button">Run a Report</a>
            <a href="batchemail.php" class="option-button">Send Batch Email</a>
        </div>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
