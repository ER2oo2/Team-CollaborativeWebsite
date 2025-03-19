<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('dbconnect.php');



if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

if (isset($_SESSION['user_session']) || isset($_SESSION['staff'])) {
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
    var_dump($_POST);
    $student_id = $_POST['student-id'];
    $first_name = $_POST['first-name'];
    $last_name = $_POST['last-name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $aid_mos = $_POST['aid-mos'];
    $aid_days = $_POST['aid-days'];

    if (empty($student_id) || empty($first_name) || empty($last_name) || empty($email) || 
      empty($address) || empty($phone) || empty($aid_mos) || empty($aid_days) || (!is_numeric($zip)) ) {
        $error = "Please fill out all fields with proper values.";
        echo $error;
        
    }

         $query = 'INSERT INTO student (stu_id, stu_fname, stu_lname, stu_address, stu_city, stu_state, 
                        stu_zip, stu_phone, stu_email, stu_aid_bal_months, stu_aid_bal_days) 
          VALUES (:student_id, :first_name, :last_name, :address, :city, :state, :zip, :phone, :email, :aid_mos, :aid_days)';
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
          $statement->bindParam(':aid_mos', $aid_mos);
          $statement->bindParam(':aid_days', $aid_days);
          $statement->execute();
          $statement->closeCursor();
          header("Location: studentrecord.php?stu_id=" . urlencode($student_id));
            exit;
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

<?php include 'navbar.php'; ?>

<main>
    <div class="form-container">
        <h2>Add New Student Record</h2>
        <form action="" method="post" class="new-record-form">
            
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

            <div class="form-group">
                <label for="aid-mos">Balance of aid months:</label>
                <input type="text" id="aid-mos" name="aid-mos" placeholder="Enter aid months left">
            </div>

            <div class="form-group">
                <label for="aid-days">Balance of aid days:</label>
                <input type="text" id="aid-days" name="aid-days" placeholder="Enter aid days left">
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
