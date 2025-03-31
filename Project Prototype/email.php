<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}

// Check if user is logged in
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

// If the form was submitted with selected student IDs...
if (isset($_POST['select-student']) && !empty($_POST['select-student'])) {
    $selected_ids = $_POST['select-student'];
    $report_results = array();
    
    // Loop through each selected student ID and fetch the record from the database
    foreach ($selected_ids as $stu_id) {
        $query = 'SELECT * FROM student WHERE stu_id = :stu_id';
        $statement = $db->prepare($query);
        $statement->bindParam(':stu_id', $stu_id);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        if ($result) {
            $report_results[] = $result;
        }
    }
    
    // Store the selected student's records for use on this page
    $_SESSION['reportResults'] = $report_results;
} else {
    // If no selected students were passed, try to use the existing session value
    if (isset($_SESSION['reportResults'])) {
        $report_results = $_SESSION['reportResults'];
    } else {
        echo "No student data was provided for emailing.";
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Students</title>
  <link rel="stylesheet" href="styles.css">
  <script>
    function sendEmail(event) {
      event.preventDefault(); // Prevent the form from submitting normally
      
      // Get the subject and body values from the form
      var subject = document.getElementById("subject").value;
      var body = document.getElementById("body").value;
      
      // Get the recipients from the generated report
      var recipients = "<?php 
          $emails = array();
          foreach ($report_results as $result) {
              $emails[] = $result['stu_email'];
          }
          echo implode(',', $emails);
      ?>";
      
      // Encode the subject and body for inclusion in a URL
      subject = encodeURIComponent(subject);
      body = encodeURIComponent(body);
      
      // Construct the mailto link with the recipients, subject, and body
      var mailtoLink = "mailto:" + recipients + "?subject=" + subject + "&body=" + body;
      
      // Open the mail client
      window.location.href = mailtoLink;
      
      return false;
    }
  </script>
</head>
<body>
  <header>
      <img src="PennWestLogo.png" alt="PennWest University Logo">
      <span>PennWest Financial Aid Veteran’s Database</span>
  </header>
  
  <?php include 'navbar.php'; ?>
  
  <main>
      <div class="email-form-container">
          <h2>Email Students</h2>
          
          <!-- Display Preselected Student(s) -->
          <div class="preselected-students">
              <h3>Selected Student(s):</h3>
              <p>
              <?php foreach ($report_results as $result) : ?>
                  <?php echo htmlspecialchars($result['stu_fname'] . ' '); ?>
                  <?php echo htmlspecialchars($result['stu_lname'] . ' '); ?>
                  <?php echo htmlspecialchars($result['stu_email'] . ', '); ?>
              <?php endforeach; ?>
              </p>
              <br>
          </div>
  
          <!-- Email Form -->
          <form onsubmit="return sendEmail(event);" class="email-form">
              <!-- Subject -->
              <div class="form-group">
                  <label for="subject">Subject:</label>
                  <input type="text" id="subject" name="subject" placeholder="Enter email subject">
              </div>
              
              <!-- Email Body -->
              <div class="form-group">
                  <label for="body">Email Body:</label>
                  <textarea id="body" name="body" rows="10" placeholder="Enter email content here..."></textarea>
              </div>
              
              <!-- Send Email Button -->
              <button type="submit" class="option-button">Send Email</button>
          </form>
      </div>
  </main>
  
  <footer>
      Pennsylvania Western University
  </footer>
</body>
</html>

