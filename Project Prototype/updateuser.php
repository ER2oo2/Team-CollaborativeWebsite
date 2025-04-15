<?php
require_once('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'], $_POST['action'])) {
    // Determine the new role based on the button clicked
    $newRole = $_POST['action'] === 'inactive' ? 'Inactive' 
             : ($_POST['action'] === 'admin' ? 'Admin' 
             : 'Support Staff');

    $query = 'UPDATE staff SET staff_role = :newRole WHERE staff_id = :staff_id';
    $statement = $db->prepare($query);
    $statement->bindParam(':newRole', $newRole);
    $statement->bindParam(':staff_id', $_POST['staff_id']);
    $statement->execute();
}

header('Location: register.php'); // Redirect back to the user management page
exit();
?>