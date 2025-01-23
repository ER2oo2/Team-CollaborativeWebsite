 /*
  Database: PennWest Military and Veteran Student Success Database
  File: veteranDatabase.sql
  Description: This file contains SQL statements to create and initialize 
               the database schema for the PennWest Military and Veteran Student Success Database
  */

CREATE TABLE student(
    stu_id VARCHAR(15) PRIMARY KEY,
    stu_fname VARCHAR(30),
    stu_lname VARCHAR(30),
    stu_street VARCHAR(30),
    stu_city VARCHAR(30),
    stu_state VARCHAR(2),
    stu_zip INT(5),
    stu_area_code INT(3),
    stu_phone VARCHAR (12), /*12 chars to account for . or - between area code and exchange*/
    stu_email VARCHAR (40),
    stu_aid_bal_months INTEGER,
    stu_aid_bal_days INTEGER    
);
/*students aren't logging in, so we don't need a student password*/

CREATE TABLE certification(
    cert_num INT AUTO_INCREMENT PRIMARY KEY,
    stu_id VARCHAR(15),
    cert_date DATE,
    cert_status TINYINT(1), /*FOR BOOLEAN VALUE- 0 IS FALSE 1 IS TRUE*/
    FOREIGN KEY (stu_id) REFERENCES student(stu_id) ON DELETE CASCADE
);

CREATE TABLE staff(
    staff_id VARCHAR(15) PRIMARY KEY,
    staff_username VARCHAR(15),
    staff_password VARCHAR(255), /*for hashed password*/
    staff_fname VARCHAR(30),
    staff_lname VARCHAR(30), 
    staff_email VARCHAR(40),
    staff_role VARCHAR(15)
);

CREATE TABLE report(
    report_num INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(15),
    report_date DATE, 
    report_type VARCHAR(15),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

CREATE TABLE email_template(
    tmplt_id INT AUTO_INCREMENT PRIMARY KEY,
    tmplt_subject VARCHAR(100),
    tmplt_body LONGTEXT,
    scheduled_email DATE
);

CREATE TABLE email_to_student(
    email_id INT AUTO_INCREMENT PRIMARY KEY,
    stu_id VARCHAR(15),
    staff_id VARCHAR(15),
    tmplt_id INT, 
    sch_email_id INT,
    FOREIGN KEY (stu_id) REFERENCES student(stu_id) ON DELETE CASCADE, 
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE, 
    FOREIGN KEY (tmplt_id) REFERENCES email_template(tmplt_id) ON DELETE CASCADE
);