<?php
	session_save_path("sessions");
	session_start();

	header('Content-type: text/html; charset=utf-8');
	header('Content-Type: application/json');
	require "model.php";

	$dbconn = connectToDatabase($db_name, $db_user, $db_password);
	
	if (!isset($_REQUEST['action'])) {
		$reply['status'] = 'error';
		print json_encode($reply);
		return;
	}
		
	if ($_REQUEST['action'] == 'auth') {
		$reply = array('auth' => 'no');
		if (isset($_SESSION['user'])) {
			$reply['auth'] = 'yes';
		}
		print json_encode($reply);
		return;
	}
	
	if ($_REQUEST['action'] == 'login') {
		$reply = array('status' => 'no');

		//check if email and password are filled in
		if (!$_REQUEST['email'] || !$_REQUEST['password']) {
			$reply['error'] = 'Please enter both email and password.';
		} else if (findUser($dbconn)) {
			$reply['status'] = 'ok';	
		} else {
			$reply['status'] = 'no';
			$reply['error'] = 'Incorrect email or password.';
		}
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == 'signup'){
		$reply = array('status' => 'no', 'error' => '');
		
		if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
			$reply['error'] .= 'Invalid email. ';
		}
		if (!checkdate($_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])) {
			$reply['error'] .= 'Invalid birthday. ';
		} 
		if($reply['error'] == ''){
			if (addUser($dbconn)) {
				$reply['status'] = 'ok';
			} else {
				$reply['error'] = 'Email has been registered.';
			}
		}
		print json_encode($reply);	
		return;
	}
	
	if($_REQUEST['action'] == "getaccount"){
		$result = getUserInfo($dbconn);

		//compute year, month and day
		$birthday = explode("-", $result['birthday']);
		$result['year'] = intval($birthday[0]);
		$result['month'] = intval($birthday[1]);
		$result['day'] = intval($birthday[2]);

		$result['status'] = 'ok';

		print json_encode($result);
		return;
	}
	
	if($_REQUEST['action'] == "updateaccount"){
		$reply = array('status' => 'no', 'error' => '');

		//validate account information
		if(!$_REQUEST['fname'] || !$_REQUEST['lname'] || !$_REQUEST['email']){
			$reply['error'] .= 'Fill in all fields.';
		}
		if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
			$reply['error'] .= 'Invalid email. ';
		}
		if (!checkdate($_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])) {
			$reply['error'] .= 'Invalid birthday. ';
		}
		if($reply['error'] == ''){
			if(updateUser($dbconn)){
				$reply['status'] = 'ok';
				$reply['msg'] = "Your information has been updated.";
			} else {
				$reply['error'] = "Update account information failed.";
			}
		}
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "updatepassword"){
		$reply = array('status' => 'no', 'error' => '');
		$result = getUserInfo($dbconn);
		
		//calculate password
		$password = hash("sha256", $_REQUEST['oldPassword'] . $result['salt']);
		$reply['password'] = $password;
		
		//validate password form
		if(!$_REQUEST['oldPassword'] || !$_REQUEST['newPassword'] || !$_REQUEST['rePassword']){
			$reply['error'] .= "Fill in all fields. ";
		}
		if ($result['password'] != $password) {
			$reply['error'] .= "Incorrect old password. ";
		}
		if($_REQUEST['newPassword'] != $_REQUEST['rePassword']){
			$reply['error'] .= "Passwords not match. ";
		}
		if($reply['error'] == '') {
			//update user password
			if(updatePassword($dbconn)) {
				$reply['status'] = 'ok';
				$reply['msg'] = "Your password has been updated.";
			} else $reply['error'] = "Update password failed.";
		}
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "gettasks"){
		$reply = array('status' => 'no');
		$result = getTasks($dbconn);

		if ($result) {
			while ($row = pg_fetch_array($result)) {
				$reply['tasks'][]=$row;
			}
			$reply['status'] = 'ok';
		}
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "undo") {
		$reply = array('status' => 'ok');
		undoTask($dbconn);
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "doit") {
		$reply = array('status' => 'ok');
		doTask($dbconn);
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "delete") {
		$reply = array('status' => 'ok');
		deleteTask($dbconn);
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "markdone") {
		$reply = array('status' => 'ok');
		doneTask($dbconn);
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "addtask") {
		$reply = array('status' => 'ok');
		addTask($dbconn);
		print json_encode($reply);
		return;
	}
	
	if ($_REQUEST['action']=="getinfo"){
		$result = getTaskInfo($dbconn);
		$result['status'] = 'ok';
		print json_encode($result);
		return;
	}
	
	if ($_REQUEST['action']=="edittask"){
		updateTask($dbconn);
		$reply = array('status' => 'ok');
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "logout"){
		unset($_SESSION['user']);
		$reply = array('status' => 'ok');
		print json_encode($reply);
		return;
	}
	
	if($_REQUEST['action'] == "rate"){
		$reply = array();
		
		$rate = caculateRate($dbconn);
		$remaining = caculateRemaining($dbconn, $rate);
		
		$reply['rate'] = $rate;
		$reply['remaining'] = $remaining;
		$reply['status'] = 'ok';
		print json_encode($reply);
		return;
	}
	
?>
