<?php
  $dsn1 = 'mysql:host=localhost;dbname=vafinaid';
    $username1 = 'vafinaid_user';
    $password1 = 'fincredit2425';
	$status = '';
   	
    try {
        $db = new PDO($dsn1, $username1, $password1);
		$status = '<p><small>*** Database Connection Status: connected ***</small></p>';
        echo $status;
    } 
	catch (PDOException $e) {
        $error_message = $e->getMessage();
        $status = 'Connection error.:$error_message';
        echo $status;
    }
	
?>