<?php
require_once('dbconnect.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure a staff member is logged in
if (!isset($_SESSION['staff'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$searchResults = [];
$cert_status_selected = "";
$aid_balance_selected = "";
$searchError = "";

// Handle the search form submission
if (isset($_POST['search'])) {
    $cert_status_selected = $_POST['cert_status'];
    $aid_balance_selected = $_POST['aid_balance'];

    $conditions = [];
    $joinClause = "LEFT JOIN benefit b ON s.benefit_type_id = b.benefit_type_id";
    $whereClause = "WHERE UPPER(b.benefit_type) <> 'INACTIVE'"; // Start with the inactive filter

    if ($cert_status_selected === "certified") {
        $conditions[] = "c.cert_status = 1";
    } elseif ($cert_status_selected === "not-certified") {
        $conditions[] = "(c.cert_status = 0 OR c.cert_status IS NULL)";
    }

    if ($aid_balance_selected === "more-than-9") {
        $conditions[] = "s.stu_aid_bal_months > 9";
    } elseif ($aid_balance_selected === "6-9") {
        $conditions[] = "s.stu_aid_bal_months BETWEEN 6 AND 9";
    } elseif ($aid_balance_selected === "3-6") {
        $conditions[] = "s.stu_aid_bal_months BETWEEN 3 AND 6";
    } elseif ($aid_balance_selected === "3-or-less") {
        $conditions[] = "s.stu_aid_bal_months <= 3";
    }

    if (!empty($conditions)) {
        $whereClause .= " AND (" . implode(" AND ", $conditions) . ")";
    }

    // Fetch students based on conditions, excluding inactive
    $query = "SELECT DISTINCT s.* FROM student s LEFT JOIN certification c ON s.stu_id = c.stu_id $joinClause $whereClause";
    $statement = $db->prepare($query);
    $statement->execute();
    $searchResults = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    if (empty($searchResults)) {
        $searchError = "No students found matching the criteria.";
    }
}

// Store selected students in session
if (isset($_POST['select-student'])) {
    $_SESSION['selected_students'] = $_POST['select-student'];
    header('Location: email.php');
    exit();
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

    <main>
        <div class="search-container">
            <h2>Select Certification Status or Aid Balance</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="cert_status">Certification Status:</label>
                    <select id="cert_status" name="cert_status">
                        <option value="">--------</option>
                        <option value="certified" <?php if ($cert_status_selected === "certified") echo "selected"; ?>>Certified</option>
                        <option value="not-certified" <?php if ($cert_status_selected === "not-certified") echo "selected"; ?>>Not Certified</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aid_balance">Aid Balance:</label>
                    <select id="aid_balance" name="aid_balance">
                        <option value="">--------</option>
                        <option value="more-than-9" <?php if ($aid_balance_selected === "more-than-9") echo "selected"; ?>>More than 9 months</option>
                        <option value="6-9" <?php if ($aid_balance_selected === "6-9") echo "selected"; ?>>6-9 months</option>
                        <option value="3-6" <?php if ($aid_balance_selected === "3-6") echo "selected"; ?>>3-6 months</option>
                        <option value="3-or-less" <?php if ($aid_balance_selected === "3-or-less") echo "selected"; ?>>3 months or less</option>
                    </select>
                </div>
                <button type="submit" name="search" class="option-button">Search</button>
            </form>
            <?php if (!empty($searchError)): ?>
                <p style="color: red;"><?php echo $searchError; ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($searchResults)): ?>
            <form action="" method="post">
                <h2 style='text-align: center'>Search Results</h2>
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
                                    <input type="checkbox" name="select-student[]" value="<?php echo htmlspecialchars($student['stu_id']); ?>"
                                        <?php echo isset($_SESSION['selected_students']) && in_array($student['stu_id'], $_SESSION['selected_students']) ? 'checked' : ''; ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Add Selected to Email</button>
            </form>
        <?php endif; ?>
    </main>

    <footer>
        Pennsylvania Western University
    </footer>
</body>
</html>