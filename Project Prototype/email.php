<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Students</title>
    <link rel="stylesheet" href="styles.css">
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
            <p>John Doe (ID: 1234567, Email: johndoe@pennwest.edu)</p>
            <!-- Additional students can be added here -->
        </div>

        <!-- Email Form -->
        <form action="#" method="post" class="email-form">
            
            <!-- Select Template -->
            <div class="form-group">
                <label for="template">Select Template:</label>
                <select id="template" name="template">
                    <option value="" disabled selected>Select a template</option>
                    <option value="welcome">Welcome Email</option>
                    <option value="financial-aid">Financial Aid Update</option>
                    <option value="reminder">Payment Reminder</option>
                    <!-- Add more templates as needed -->
                </select>
            </div>
            
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
            
            <!-- Schedule Batch Emails -->
            <div class="form-group">
                <label for="schedule">Schedule Batch Emails:</label>
                <select id="schedule" name="schedule">
                    <option value="now">Send Immediately</option>
                    <option value="1-hour">In 1 Hour</option>
                    <option value="1-day">In 1 Day</option>
                    <option value="1-week">In 1 Week</option>
                    <!-- Additional scheduling options as needed -->
                </select>
            </div>
            <!-- Save Template Button -->
            <button type="submit" class="option-button">Save Template</button>
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
