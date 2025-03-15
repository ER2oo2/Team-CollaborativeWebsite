<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <img src="PennWestLogo.png" alt="PennWest University Logo">
    <span>PennWest Financial Aid Veteran’s Database</span>
</header>

<?php include 'navbar.php'; ?>

<main>
    <div class="options-container">
        <h2>Select an Action</h2>
        <div class="option-buttons">
            <a href="search.php" class="option-button">Search</a>
            <a href="newrecord.php" class="option-button">Add New Record</a>
            <a href="reports.php" class="option-button">Run a Report</a>
            <a href="email.php" class="option-button">Send Batch Email</a>
        </div>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
