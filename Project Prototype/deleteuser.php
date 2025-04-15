<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Allow only Admins to delete users
if (!isset($_SESSION['staff']['staff_role']) || $_SESSION['staff']['staff_role'] !== 'Admin') {
    header('Location: ' . $_SERVER['HTTP_REFERER']); // Redirect back
    exit;
}

// Ensure a user ID is received
if (isset($_POST['staff_id'])) {
    $staff_id = filter_input(INPUT_POST, 'staff_id');

    // Prevent deletion of currently logged-in user
    if ($staff_id === $_SESSION['staff']['staff_id']) {
        echo "You cannot delete yourself!";
        exit;
    }

    // Prepare and execute delete query
    $query = 'DELETE FROM staff WHERE staff_id = :staff_id';
    $statement = $db->prepare($query);
    $statement->bindParam(':staff_id', $staff_id);

    if ($statement->execute()) {
        header('Location: register.php'); // Refresh the page after deletion
        exit;
    } else {
        echo "Error deleting user.";
    }
} else {
    echo "Invalid request.";
}
?>