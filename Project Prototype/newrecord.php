<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

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

if (isset($_POST['student-id'])) {
    $student_id = $_POST['student-id'];
    $first_name = $_POST['first-name'];
    $last_name = $_POST['last-name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email || 
      empty($address) || empty($phone))) {
        $error = "Please fill out all fields.";
        echo $error;
        exit;
    }

    if (empty($error)) {
            $query = 'INSERT INTO student (stu_id, stu_fname, stu_lname, stu_address, stu_phone, stu_email) 
              VALUES (:student_id, :first_name, :last_name, :address, :phone, :email)';
        $statement = $db->prepare($query);
        $statement->bindParam(':student_id', $student_id);
        $statement->bindParam(':first_name', $first_name);
        $statement->bindParam(':last_name', $last_name);
        $statement->bindParam(':address', $address);
        $statement->bindParam(':city', $city);
        $statement->bindParam(':state', $state);
        $statement->bindParam(':zip', $zip);
        $statement->bindParam(':phone', $phone);
        $statement->bindParam(':email', $email);
        $statement->execute();
        $statement->closeCursor();

        //successful insert, redirect to student record page
        if($statement->execute()) {
            header("Location: studentrecord.php?stu_id=" . urlencode($studentId));
        } else {
            $error = "Error adding student record.";
            echo $error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Record</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <img src="PennWestLogo.png" alt="PennWest University Logo">
    <span>PennWest Financial Aid Veteran’s Database</span>
</header>

<nav>
    <a href="index.html">Home</a>
    <a href="search.html">Search</a>
    <a href="newrecord.html">New Record</a>
    <a href="email.html">Email</a>
    <a href="reports.html">Reports</a>
    &ensp;&ensp;&ensp;&ensp;<a href="login.html">Login</a>
    <a href="#logout">| Logout</a>
</nav>

<main>
    <div class="form-container">
        <h2>Add New Student Record</h2>
        <form action="#" method="post" class="new-record-form">
            
            <!-- Student ID (Required) -->
            <div class="form-group">
                <label for="student-id">Student ID <span style="color: red;">*</span>:</label>
                <input type="text" id="student-id" name="student-id" required placeholder="Enter student ID">
            </div>
            
            <!-- First Name (Required) -->
            <div class="form-group">
                <label for="first-name">First Name <span style="color: red;">*</span>:</label>
                <input type="text" id="first-name" name="first-name" required placeholder="Enter first name">
            </div>
            
            <!-- Last Name (Required) -->
            <div class="form-group">
                <label for="last-name">Last Name <span style="color: red;">*</span>:</label>
                <input type="text" id="last-name" name="last-name" required placeholder="Enter last name">
            </div>
            
            <!-- Address -->
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" placeholder="Enter address">
            </div>

            <!-- City -->
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" placeholder="Enter city"> 
            </div>

            <!-- State -->
            <div class="form-group">
                <label for="state">State:</label>
                <input type="text" id="state" name="state" placeholder="Enter state">
            </div>

            <!-- Zip Code -->
            <div class="form-group">
                <label for="zip">Zip Code:</label>
                <input type="text" id="zip" name="zip" placeholder="Enter zip code">
            </div>
            
            <!-- Phone Number -->
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter phone number">
            </div>
            
            <!-- Email (Required) -->
            <div class="form-group">
                <label for="email">Email <span style="color: red;">*</span>:</label>
                <input type="email" id="email" name="email" required placeholder="Enter email address">
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="option-button">Submit</button>
            
            <!-- Required Field Note -->
            <p style="color: red; font-size: 0.9em; margin-top: 10px;">* Required fields</p>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
