<?php
//connect to database
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


//select search method and store user input 
if (isset($_POST['search-option'])) {
    $searchOption = $_POST['search-option'];

    if ($searchOption == 'name') {
        $first_name = $_POST['first-name'];
        $last_name = $_POST['last-name'];     
        
       //sql code to search by name
       $query = 'SELECT * FROM student WHERE stu_fname LIKE :firstName and stu_lname LIKE :lastName';
       $statement = $db->prepare($query);
       $statement->bindParam(':firstName', $first_name);
       $statement->bindParam(':lastName', $last_name); 
       $statement->execute();
       $student = $statement->fetch();
       $statement->closeCursor();

    } elseif ($searchOption == 'id') {
        $student_id = $_POST['student-id'];

        //sql code to search by student id
        $query = 'SELECT * FROM student WHERE stu_id = :student_id';
        $statement = $db->prepare($query);
        $statement->bindParam(':student_id', $student_id);
        $statement->execute();
        $student = $statement->fetch();
        $statement->closeCursor();

    } elseif ($searchOption == 'non-certified') {
        //sql code to search for non-certified students for current semester- use left join to include students with no certifications
        $query = 'SELECT student.stu_id, student.stu_fname, student.stu_lname
                    FROM student
                    LEFT JOIN certification ON student.stu_id = certification.stu_id
                    WHERE certification.cert_status = 0 or certification.cert_status IS NULL';
        $statement = $db->prepare($query);
        $statement->execute();
        $students = $statement->fetchAll();
        $statement->closeCursor();
    }

    //save serach results to session
    $_SESSION['searchResults'] = $students;

    //redirect to search results page
    header('Location: searchResults.php');
    exit();
  
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Students</title>
    <link rel="stylesheet" href="styles.css">
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
<div class="search-container">
        <h2>Search Students</h2>
        <form action="#" method="post" class="search-form">
            
            <!-- Search by Name -->
            <div class="form-group">
                <input type="radio" id="search-by-name" name="search-option" value="name">
                <label for="search-by-name">Search by Student Name:</label>
		<br>&ensp;&ensp;&ensp;&ensp;
                <label for="first-name">First Name:</label>
                <input type="text" id="first-name" name="first-name" placeholder="Enter first name">
                <br>&ensp;&ensp;&ensp;&ensp;
		<label for="last-name">Last Name:</label>
                <input type="text" id="last-name" name="last-name" placeholder="Enter last name">
            </div>
            
            <!-- Search by Student ID -->
            <div class="form-group">
                <input type="radio" id="search-by-id" name="search-option" value="id">
                <label for="search-by-id">Search by Student ID:</label>
                <input type="text" id="student-id" name="student-id" placeholder="Enter student ID">
            </div>
            
            <!-- Search for Non-Certified Students for Current Semester -->
            <div class="form-group">
                <input type="radio" id="search-non-certified" name="search-option" value="non-certified">
                <label for="search-non-certified">Search for Non-Certified Students for Current Semester</label>
            </div>
            
            <!-- Single Search Button -->
            <button type="submit" class="option-button">Search</button>
        </form>
    </div>
</main>

<footer>
    Pennsylvania Western University
</footer>

</body>
</html>
