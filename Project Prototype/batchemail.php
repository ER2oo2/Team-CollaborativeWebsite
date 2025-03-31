<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

// Check if a staff member is logged in
if (!isset($_SESSION['staff'])) {
    echo "No user is logged in";
    header('Location: login.php');
    exit();
}

// Initialize variables
$searchResults       = array();
$cert_status_selected = "";
$aid_balance_selected = "";
$searchError         = "";

// Process the search form submission
if (isset($_POST['search'])) {
    // Retrieve search criteria from the form
    $cert_status_selected = $_POST['cert_status'];  
    $aid_balance_selected = $_POST['aid_balance'];  

    // Build dynamic query conditions
    $conditions = array();

    // Certification status condition
    if ($cert_status_selected === "certified") {
        $conditions[] = "c.cert_status = 1";
    } elseif ($cert_status_selected === "not-certified") {
        $conditions[] = "(c.cert_status = 0 OR c.cert_status IS NULL)";
    }

    // Aid balance condition (using the student table column "stu_aid_bal_months")
    if ($aid_balance_selected === "more-than-9") {
        $conditions[] = "s.stu_aid_bal_months > 9";
    } elseif ($aid_balance_selected === "6-9") {
        $conditions[] = "s.stu_aid_bal_months BETWEEN 6 AND 9";
    } elseif ($aid_balance_selected === "3-6") {
        $conditions[] = "s.stu_aid_bal_months BETWEEN 3 AND 6";
    } elseif ($aid_balance_selected === "3-or-less") {
        $conditions[] = "s.stu_aid_bal_months <= 3";
    }

    
    $whereClause = "";
    if (count($conditions) > 0) {
        $whereClause = "WHERE " . implode(" AND ", $conditions);
    }

    // Build and execute the query. Use a LEFT JOIN so that even students without certification records appear.
    $query = "SELECT DISTINCT s.* 
              FROM student s 
              LEFT JOIN certification c ON s.stu_id = c.stu_id 
              $whereClause";
    $statement = $db->prepare($query);
    $statement->execute();
    $searchResults = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    // Set an error message if no records were found
    if (empty($searchResults)) {
        $searchError = "No students found matching those criteria.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Students - Advanced Search</title>
  <link rel="stylesheet" href="styles.css">
  <script>
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('input[name="select-student[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }
   </script>
</head>
<body>
  <header>
      <img src="PennWestLogo.png" alt="PennWest University Logo">
      <span>PennWest Financial Aid Veteran’s Database</span>
  </header>

  <?php include 'navbar.php'; ?>
 <h1 style="color:black; text-decoration:underline; padding:5%">Batch Email</h1>
  <main>
    <div class="search-container">
        <br>
          <h2>Select Certification Status or Aid Balance</h2>
          <!-- Search Form -->
          <form action="" method="post" class="search-form">
              <!-- Certification Status Select -->
              <div class="form-group">
                  <label for="cert_status">Certification Status:</label>
                  <select id="cert_status" name="cert_status">
                      <option value="" selected>Make a selection</option>
                      <option value="certified" <?php if ($cert_status_selected === "certified") echo "selected"; ?>>Certified</option>
                      <option value="not-certified" <?php if ($cert_status_selected === "not-certified") echo "selected"; ?>>Not Certified</option>
                  </select>
              </div>
              <br>
              <!-- Aid Balance Select -->
              <div class="form-group">
                  <label for="aid_balance">Aid Balance:</label>
                  <select id="aid_balance" name="aid_balance">
                      <option value="" selected>Make a selection</option>
                      <option value="more-than-9" <?php if ($aid_balance_selected === "more-than-9") echo "selected"; ?>>More than 9 months</option>
                      <option value="6-9" <?php if ($aid_balance_selected === "6-9") echo "selected"; ?>>6-9 months</option>
                      <option value="3-6" <?php if ($aid_balance_selected === "3-6") echo "selected"; ?>>3-6 months</option>
                      <option value="3-or-less" <?php if ($aid_balance_selected === "3-or-less") echo "selected"; ?>>3 months or less</option>
                  </select>
              </div>
              <!-- Search Button -->
              <div class="form-group">
                  <button type="submit" name="search" class="option-button">Search</button>
              </div>
          </form>
          <!-- Display an error message if no records are found -->
          <?php if (!empty($searchError)): ?>
              <p style="color: red;"><?php echo $searchError; ?></p>
          <?php endif; ?>
    </div>

    <div class="results-container">
          <?php if (!empty($searchResults)): ?>
              <h2>Search Results</h2>
              <form action="email.php" method="post">
                  <table>
                      <thead>
                          <tr>
                              <th>Student ID</th>
                              <th>Last Name</th>
                              <th>First Name</th>
                              <th>Email</th>
                              <th>Select All<br>
                                <input type="checkbox" onclick="toggleSelectAll(this)">
                              </th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($searchResults as $student): ?>
                              <tr>
                                  <td><?php echo htmlspecialchars($student['stu_id']); ?></td>
                                  <td><?php echo htmlspecialchars($student['stu_lname']); ?></td>
                                  <td><?php echo htmlspecialchars($student['stu_fname']); ?></td>
                                  <td><?php echo htmlspecialchars($student['stu_email']); ?></td>
                                  <td>
                                      <input type="checkbox" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>">
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
                  <div>
                      <button type="submit" class="option-button">Add Selected to Email</button>
                  </div>
              </form>
          <?php endif; ?>
    </div>
 </main>

<footer>
    Pennsylvania Western University
</footer>
</body>
</html>