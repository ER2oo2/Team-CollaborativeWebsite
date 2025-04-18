 /*
  Database: PennWest Military and Veteran Student Success Database
  File: veteranDatabase.sql
  Description: This file contains SQL statements to create and initialize 
     the database schema for the PennWest Military and Veteran Student Success Database
  */

CREATE TABLE benefit(
    benefit_type_id INT AUTO_INCREMENT PRIMARY KEY,
    benefit_type VARCHAR(25) UNIQUE NOT NULL
);

CREATE TABLE student(
    stu_id VARCHAR(15) PRIMARY KEY,
    stu_fname VARCHAR(30),
    stu_lname VARCHAR(30),
    stu_address VARCHAR(30),
    stu_city VARCHAR(30),
    stu_state VARCHAR(2),
    stu_zip VARCHAR(10),
    stu_phone VARCHAR (16), /*16 chars to account for . or - between area code and exchange*/
    stu_email VARCHAR (40),
    stu_aid_bal_months INTEGER,
    stu_aid_bal_days INTEGER,
    benefit_type_id INTEGER,
    FOREIGN KEY (benefit_type_id) REFERENCES benefit(benefit_type_id) ON DELETE CASCADE    
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
    staff_username VARCHAR(15) UNIQUE NOT NULL,
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
    tmplt_body LONGTEXT
);

CREATE TABLE email_to_student(
    email_id INT AUTO_INCREMENT PRIMARY KEY,
    stu_id VARCHAR(15),
    staff_id VARCHAR(15),
    tmplt_id INT,
    date_sent DATETIME,
    FOREIGN KEY (stu_id) REFERENCES student(stu_id) ON DELETE CASCADE, 
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE SET NULL, 
    FOREIGN KEY (tmplt_id) REFERENCES email_template(tmplt_id) ON DELETE CASCADE
);

/* Automatically insert the 'INACTIVE' benefit type */
INSERT INTO benefit (benefit_type) VALUES ('INACTIVE');

INSERT INTO staff (staff_id, staff_username, staff_password, staff_fname, staff_lname, staff_email, staff_role) VALUES
                  (12345678, 'test', '$2y$10$.fmIxS0q9LPGCqQjI/o7YOB8VmHZ.lAYnLwHz6KAzKtSxsHB2ySHG', 'test', 'test', 'test@test.com', 'Admin')  