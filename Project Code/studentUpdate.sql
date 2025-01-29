 /*
  When we do PHP scripting, this script will be removed. Currently, the SQL updates EVERY column. 
  */


UPDATE student
SET 
    stu_fname = ?, 
    stu_lname = ?, 
    stu_street = ?, 
    stu_city = ?, 
    stu_state = ?, 
    stu_zip = ?, 
    stu_area_code = ?, 
    stu_phone = ?, 
    stu_email = ?, 
    stu_aid_bal_months = ?, 
    stu_aid_bal_days = ?
WHERE stu_id = ?;