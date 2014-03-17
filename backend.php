<?php
session_save_path("sess");
session_start();

header('Content-Type: application/json');
require 'config.inc';

if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action'] == 'auth') {
		$reply = array();
		if (isset($_SESSION['user'])) {
			$reply['auth'] = 'yes';
		} else {
			$reply['auth'] = 'no';
		}

		print json_encode($reply);
	} elseif ($_REQUEST['action'] == 'login') {
		$reply = array();
		$email = $_REQUEST['email'];
		$password = $_REQUEST['password'];

		//check if email and password are filled in
		if (!$email || !$password) {
			$reply['status'] = 'no';
			$reply['error'] = 'Please enter both email and password.';
			print json_encode($reply);
		} else {
			//find a match in database
			$dbconn = connectToDatabase($db_name, $db_user, $db_password);
			$select_user_query = ("SELECT * FROM appuser WHERE email = $1 AND password = $2;");
			$result = pg_prepare($dbconn, "select_user", $select_user_query);
			$result = pg_execute($dbconn, "select_user", array($email, md5($password)));

			if(pg_num_rows($result)){
				$row = pg_fetch_array($result);
				$_SESSION['user'] = $row['uid'];
				$reply['status'] = 'ok';
			} else {
				$reply['status'] = 'no';
				$reply['error'] = "Invalid user or password.";
			}

			print json_encode($reply);
		}
	} else if($_REQUEST['action'] == 'signup'){
		$reply = array();

		if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
			$reply['status'] = 'no';
			$reply['error'] = 'Please enter a valid email.';
			print json_encode($reply);
		} else if (!checkdate($_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])) {
			$reply['status'] = 'no';
			$reply['error'] = 'Please enter a valid birthday.';
			print json_encode($reply);
		} else {
			//check if this email is already inside database
			$dbconn = connectToDatabase($db_name, $db_user, $db_password);
			$select_user_query = ("SELECT * FROM appuser WHERE email = $1;");
			$result = pg_prepare($dbconn, "select_user", $select_user_query);
			$result = pg_execute($dbconn, "select_user", array($_REQUEST['email']));
			
			if(pg_num_rows($result)){
				$reply['status'] = 'no';
				$reply['error'] = 'Email has been registered.';
				print json_encode($reply);
			} else {
				//insert into database
				$year = $_REQUEST['year'];
				$month = $_REQUEST['month'];
				$day = $_REQUEST['day'];
				$insert_user_query = "INSERT INTO appuser (email, fname, lname, password, birthday, signupdate, news, sex, done) VALUES($1, $2, $3, $4, $5, $6, $7, $8, 0);";
				$result = pg_prepare($dbconn, "insert_user", $insert_user_query);
				$result = pg_execute($dbconn, "insert_user", array($_REQUEST['email'], $_REQUEST['fname'], $_REQUEST['lname'], md5($_REQUEST['password']), "$year-$month-$day", date("Y-m-d"), $_REQUEST['news'], $_REQUEST['sex']));

				$reply['status'] = 'ok';
				print json_encode($reply);
			}
		}






	}
}











function connectToDatabase($db_name, $db_user, $db_password){
        $dbconn = pg_connect("host=localhost port=5432 dbname=$db_name user=$db_user password=$db_password");
        if(!$dbconn){
        	$reply = array();
        	$reply['status'] = "Aw, Snap!";
            //echo "Aw, Snap!";
            print json_encode($reply);    
        }

        return $dbconn; 
    }













?>