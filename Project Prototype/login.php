<?php
require_once 'dbconnect.php';

// Check if the user is already logged in
$status = session_status();
if ($status == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['staff'])) {
    echo "User is already logged in";
    header('Location: index.php'); // Redirect to the index if already logged in
    exit();
}

// Check if the login form was submitted
if (isset($_POST['username'])) {
    // Retrieve user input (email and password)
    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST,'password');

    // Query the database to check if the provided email exists and matches the password
    $query = 'SELECT staff_id, staff_username, staff_password, staff_fname, staff_lname, staff_email, staff_role
              FROM staff 
              WHERE staff_username = :username';
    $statement = $db->prepare($query);
    $statement->bindParam(':username', $username);
    $statement->execute();
    $staff = $statement->fetch();
    $statement->closeCursor();
	
    if ($staff) {
        // Verify the entered password against the stored hashed password contained in $user['password']
        if (password_verify($password, $staff['staff_password'])) {
            // Successful login
            $_SESSION['staff'] = array(
                'staff_username' => $staff['staff_username'],
                'staff_fname' => $staff['staff_fname'],
                'staff_lname' => $staff['staff_lname'],
                'staff_email' => $staff['staff_email'],
                'staff_role' => $staff['staff_role']
            );
            header('Location: index.php');
            exit();
        } else {
            $error_message = 'Invalid login credentials';
			echo $error_message;
        }
    } else {
        $error_message = 'Invalid login credentials';
		echo $error_message;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Screen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <img src="PennWestLogo.png" alt="PennWest University Logo">
    <span>PennWest Financial Aid Veteran’s Database</span>
</header>

<?php include 'navbar.php'; ?>

<main>
    <div class="login-form">
        <h2>Login</h2>
        <form action="#" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
