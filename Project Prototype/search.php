<?php
//connect to database
require_once('dbconnect.php');

//validate if there is a session, if not- start one
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//checking to see if SESSION variables passed correctly
if (isset($_SESSION['staff'])) {
    $staff_id = $_SESSION['staff']['staff_username'];
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

// Initialize an error message variable
$searchError = "";

//select search method and store user input
if (isset($_POST['search-option'])) {
    $searchOption = $_POST['search-option'];

    if ($searchOption == 'name') {
        $first_name = $_POST['first-name'];
        $last_name = $_POST['last-name'];

        //sql code to search by name
        $query = 'SELECT * FROM student WHERE stu_fname LIKE :firstName AND stu_lname LIKE :lastName';
        $statement = $db->prepare($query);
        $first_name_wildcard = '%' . $first_name . '%';
        $last_name_wildcard = '%' . $last_name . '%';
        $statement->bindParam(':firstName', $first_name_wildcard);
        $statement->bindParam(':lastName', $last_name_wildcard);
        $statement->execute();
        $student = $statement->fetchAll();
        $statement->closeCursor();
    } elseif ($searchOption == 'id') {
        $student_id = $_POST['student-id'];

        //sql code to search by student id
        $query = 'SELECT * FROM student WHERE stu_id = :student_id';
        $statement = $db->prepare($query);
        $statement->bindParam(':student_id', $student_id);
        $statement->execute();
        $student = $statement->fetchAll();
        $statement->closeCursor();
    } elseif ($searchOption == 'non-certified') {
        //sql code to search for non-certified students
        $query = 'SELECT * 
                  FROM student
                  LEFT JOIN certification ON student.stu_id = certification.stu_id
                  WHERE certification.cert_status = 0 OR certification.cert_status IS NULL';
        $statement = $db->prepare($query);
        $statement->execute();
        $student = $statement->fetchAll();
        $statement->closeCursor();
    } elseif ($searchOption == 'aid-balance') {
        $aid_balance = $_POST['aid-balance'];

        // Adjust SQL query based on dropdown value with the correct column name:
        if ($aid_balance == 'more-than-9') {
            $query = 'SELECT * FROM student WHERE stu_aid_bal_months > 9';
        } elseif ($aid_balance == '6-9') {
            $query = 'SELECT * FROM student WHERE stu_aid_bal_months BETWEEN 6 AND 9';
        } elseif ($aid_balance == '3-6') {
            $query = 'SELECT * FROM student WHERE stu_aid_bal_months BETWEEN 3 AND 6';
        } elseif ($aid_balance == '3-or-less') {
            $query = 'SELECT * FROM student WHERE stu_aid_bal_months <= 3';
        }

        $statement = $db->prepare($query);
        $statement->execute();
        $student = $statement->fetchAll();
        $statement->closeCursor();
    } elseif ($searchOption == 'benefit-type') {
        $benefit_type = $_POST['benefit-type'];

        // SQL code to search by benefit type
        $query = 'SELECT student.*, benefit.benefit_type 
                  FROM student 
                  LEFT JOIN benefit ON student.benefit_type_id = benefit.benefit_type_id
                  WHERE benefit.benefit_type_id = :benefit_type';
        $statement = $db->prepare($query);
        $statement->bindParam(':benefit_type', $benefit_type, PDO::PARAM_INT);
        $statement->execute();
        $student = $statement->fetchAll();
        $statement->closeCursor();
    }

    // If no results are found, set an error message; otherwise, store results and redirect.
    if (empty($student)) {
        $searchError = "No results found. Check your search criteria and try again.";
    } else {
        // Save search results to session and redirect if results exist
        $_SESSION['searchResults'] = $student;
        header('Location: searchresults.php');
        exit();
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veteran DB: Search Students</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header>
        <img src="PennWestLogo.png" alt="PennWest University Logo">
        <span>PennWest Financial Aid Veteran’s Database</span>
    </header>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="search-container">
            <h2>Search Students</h2>
            <?php
            if (!empty($searchError)) {
                echo "<p style='color:red;'>$searchError</p>";
            }
            ?>
            <form action="search.php" method="post" class="search-form">

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

                <div class="form-group">
                    <input type="radio" id="search-by-id" name="search-option" value="id">
                    <label for="search-by-id">Search by Student ID:</label>
                    <input type="text" id="student-id" name="student-id" placeholder="Enter student ID">
                </div>

                <div class="form-group">
                    <input type="radio" id="search-non-certified" name="search-option" value="non-certified">
                    <label for="search-non-certified">Search for Non-Certified Students for Current Semester</label>
                </div>

                <div class="form-group">
                    <input type="radio" id="search-months-of-aid" name="search-option" value="aid-balance">
                    <label for="search-months-of-aid">Search by Benefit Balance:</label>
                    <select id="aid-balance" name="aid-balance">
                        <option value="" disabled selected>Make a selection</option>
                        <option value="more-than-9">More than 9 months</option>
                        <option value="6-9">6-9 months</option>
                        <option value="3-6">3-6 months</option>
                        <option value="3-or-less">3 months or less</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="radio" id="search-by-benefit-type" name="search-option" value="benefit-type">
                    <label for="search-by-benefit-type">Search by Benefit Type:</label>
                    <select id="benefit-type" name="benefit-type">
                        <option value="" disabled selected>Make a selection</option>
                        <?php
                        // Fetch benefit types from the database
                        $query = "SELECT benefit_type_id, benefit_type FROM benefit WHERE UPPER(benefit_type) <> 'INACTIVE' ORDER BY benefit_type";
                        $statement = $db->prepare($query);
                        $statement->execute();
                        $benefitTypes = $statement->fetchAll(PDO::FETCH_ASSOC);
                        $statement->closeCursor();

                        foreach ($benefitTypes as $type) {
                            echo '<option value="' . htmlspecialchars($type['benefit_type_id']) . '">' . htmlspecialchars($type['benefit_type']) . '</option>';
                        }
                        ?>
                    </select>
                </div>


                <button type="submit" class="option-button">Search</button>
            </form>
        </div>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>

</body>

</html>
<?php ob_end_flush(); ?>