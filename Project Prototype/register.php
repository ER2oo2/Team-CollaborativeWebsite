<?php
require_once('dbconnect.php');

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['staff']['staff_role']) || $_SESSION['staff']['staff_role'] !== 'Admin') {
    header('Location: ' . $_SERVER['HTTP_REFERER']); // Redirect to previous page
    exit;
}
// Initialize variables
$username = $password = $password2 = $staff_id = $fname = $lname = $email = $role = '';
$errors = '';


// Fetch existing users for deletion except the currently logged-in user
$query = 'SELECT staff_id, staff_username, staff_role FROM staff WHERE staff_id != :logged_in_user';
$statement = $db->prepare($query);
$statement->bindParam(':logged_in_user', $_SESSION['staff']['staff_id']);
$statement->execute();
$staffList = $statement->fetchAll();

$register = filter_input(INPUT_POST, 'register');
if (isset($register)) {
    // Validate and sanitize user input
    $username = filter_input(INPUT_POST, 'username');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password');
    $password2 = filter_input(INPUT_POST, 'password2');
    $staff_id = filter_input(INPUT_POST, 'staff_id');
    $fname = filter_input(INPUT_POST, 'fname');
    $lname = filter_input(INPUT_POST, 'lname');
    $role = filter_input(INPUT_POST, 'role');

    // Check for empty fields
    if (empty($username)) {
        $errors = 'Username is required';
    }
    if (empty($email)) {
        $errors = 'Email is required';
    }
    if (empty($password)) {
        $errors = 'Password is required';
    }
    if (empty($fname)) {
        $errors = 'First Name is required';
    }
    if (empty($lname)) {
        $errors = 'Last Name is required';
    }
    if (empty($role)) {
        $errors = 'Please select a role';
    }

    // Check if passwords match
    if ($password != $password2) {
        $errors = 'Passwords do not match';
    }
    if (!in_array($role, ['Admin', 'Support Staff'])) {
        $errors = 'Invalid role selected';
    }

    // If there are no validation errors, proceed with registration
    if (empty($errors)) {
        // Hash the password before storing it, PASSWORD_DEFAULT seeds the has generator.
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = 'SELECT * FROM staff WHERE staff_username = :username OR staff_email = :email';
        $statement = $db->prepare($query);
        $statement->bindParam(':username', $username);
        $statement->bindParam(':email', $email);
        $statement->execute();

        if ($statement->fetch()) {
            $errors = "Username or Email already exists!";
        } else {
            // SQL query to insert the new user into the database
            $query = 'INSERT INTO staff(staff_id, staff_username, staff_password, staff_fname, staff_lname, staff_email, staff_role)
                        VALUES (:staff_id, :username, :password, :fname, :lname, :email, :role)';
            $statement = $db->prepare($query);
            $statement->bindParam(':staff_id', $staff_id);
            $statement->bindParam(':username', $username);
            $statement->bindParam(':password', $hashedPassword);
            $statement->bindParam(':fname', $fname);
            $statement->bindParam(':lname', $lname);
            $statement->bindParam(':email', $email);
            $statement->bindParam(':role', $role);

            if ($statement->execute()) {
                // Registration successful, redirect to login page
                header('Location: login.php');
                exit; // Important: Stop further execution after redirect
            } else {
                // Registration failed, display an error message
                $errorInfo = $statement->errorInfo(); // Get SQL error details
                $errors = "SQL Error: " . $errorInfo[2];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: User Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database</span>
    </header>

    <?php include 'navbar.php'; ?>

    <?php if (!empty($errors)) : ?>
        <p class="error"><?php echo $errors; ?></p>
    <?php endif; ?>

    <main>
        <div class="form-container" style="border: 1px solid #ccc; padding: 10px; margin: 10px;">
            <h2 style="text-align: center";>Create a New User</h2>
            <form action="register.php" method="post" class="new-record-form">

                <div class="form-group">
                    <label for="">User/Staff ID:</label>
                    <input type="text" id="staff_id" name="staff_id" required placeholder="Enter staff ID">
                </div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required placeholder="Enter a username">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Enter a password">
                </div>

                <div class="form-group">
                    <label for="password2">Confirm password:</label>
                    <input type="password" id="password2" name="password2" required placeholder="Confirm password">
                </div>

                <div class="form-group">
                    <label for="fname">First Name: </label>
                    <input type="text" id="fname" name="fname" required placeholder="Enter first name">
                </div>

                <div class="form-group">
                    <label for="lname">Last Name: </label>
                    <input type="text" id="lname" name="lname" required placeholder="Enter last name">
                </div>

                <div class="form-group">
                    <label for="email">Email: </label>
                    <input type="email" id="email" name="email" required placeholder="Enter email address">
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>--Select a role--</option>
                        <option value="Support Staff">Support Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div class="options-container">
                    <div class="option-buttons">
                        <button type="submit" class="option-button" name="register">Submit</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Display existing users for deletion -->
        <div class="form-container" style="border: 1px solid #ccc; padding: 10px; margin: 10px;">
            <h2 style="text-align: center" ;>Delete Users</h2>

            <table>
                <tr>
                    <th>Staff ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($staff['staff_id']); ?></td>
                        <td><?php echo htmlspecialchars($staff['staff_username']); ?></td>
                        <td><?php echo htmlspecialchars($staff['staff_role']); ?></td>
                        <td>
                            <form action="deleteuser.php" method="post">
                                <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staff['staff_id']); ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>

</body>

</html>