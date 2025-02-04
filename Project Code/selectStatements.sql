/*Select statement to query all students with only student id  and
aid balance displayed*/

$query = 'SELECT stu_id,
                 stu_fname,
                 stu_lname,
                 stu_aid_bal_months,
                 stu_aid_bal_days 
          FROM student
          ORDER BY stu_id';
$statement1 = $db->prepare($query);
$statement1->execute();
$selectedCert = $statement1->fetchAll();
$statement1->closeCursor();


/*Select statment and PHP code for specific student where variable $stu_id is use
in the PHP code */
 
$query = 'SELECT * FROM student WHERE stu_id = :stu_id';
$statement1 = $db->prepare($query);
$statement1->bindParam(':stu_id', $stu_id);
$statement1->execute();
$selectedStudent = $statement1->fetchAll();
$statement1->closeCursor();

/*Select statement for certifications where $stu_id is set as a php variable to show 
certifications for a specific student*/

$query = 'SELECT * FROM certification WHERE stu_id = :stu_id';
$statement1 = $db->prepare($query);
$statement1->bindParam(':stu_id', $stu_id);
$statement1->execute();
$selectedCert = $statement1->fetchAll();
$statement1->closeCursor();

/*Queries email to student table to show all emails sent to each student 
referencing the specific student ID as a php variable of $stu_id*/

$query = 'SELECT * FROM email_to_student WHERE stu_id = :stu_id';
$statement1 = $db->prepare($query);
$statement1->bindParam(':stu_id', $stu_id);
$statement1->execute();
$selectedCert = $statement1->fetchAll();
$statement1->closeCursor();


/*Select statement for email templates to view template info*/

$query = 'SELECT * FROM email_template';
$statement1 = $db->prepare($query);
$statement1->execute();
$selectedCert = $statement1->fetchAll();
$statement1->closeCursor();
