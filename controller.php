<?php
	session_save_path("sess");
	session_start();

	header('Content-Type: application/json');
	require "model.php";
	
	if (isset($_REQUEST['action'])) {
		if ($_REQUEST['action'] == 'auth') {
			$reply = array('auth' => 'no');
			if (isset($_SESSION['user'])) {
				$reply['auth'] = 'yes';
			}
			print json_encode($reply);

		} else if ($_REQUEST['action'] == 'login') {
			$reply = array('status' => 'no');

			//check if email and password are filled in
			if (!$_REQUEST['email'] || !$_REQUEST['password']) {
				$reply['error'] = 'Please enter both email and password.';
				print json_encode($reply);
			} else if (findUser()) {
				$reply['status'] = 'ok';	
			} 
			print json_encode($reply);

		} else if($_REQUEST['action'] == 'signup'){
			$reply = array('status' => 'no');
			
			if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
				$reply['error'] = 'Please enter a valid email.';
			} else if (!checkdate($_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])) {
				$reply['error'] = 'Please enter a valid birthday.';
			} else if (addUser()) {
				$reply['status'] = 'ok';
			} else {
				$reply['error'] = 'Email has been registered.';
			}
			print json_encode($reply);	

		} else if($_REQUEST['action'] == "getaccount"){
			$result = getUserInfo();

			//compute year, month and day
			$birthday = explode("-", $result['birthday']);
			$result['year'] = intval($birthday[0]);
			$result['month'] = intval($birthday[1]);
			$result['day'] = intval($birthday[2]);

			$result['status'] = 'ok';

			print json_encode($result);	
		} else if($_REQUEST['action'] == "updateaccount"){
			$reply = array('status' => 'no');

			//validate account information
			if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
				$reply['error'] = 'Please enter a valid email.';
			} else if (!checkdate($_REQUEST['month'], $_REQUEST['day'], $_REQUEST['year'])) {
				$reply['error'] = 'Please enter a valid birthday.';
			} else if(updateUser()){
				$reply['status'] = 'ok';
				$reply['msg'] = "Your information has been updated.";
			} else {
				$reply['error'] = "Update account information failed.";
			}
			print json_encode($reply);
		} else if($_REQUEST['action'] == "updatepassword"){
			$reply = array('status' => 'no');
			$result = getUserInfo();
			$password = $result['password'];

			//validate password form
			if (md5($_POST['old-password']) != $password) {
				$reply['error'] = "Please enter correct old password.";
			} else {
				//update user password
				if(updatePassword()) {
					$reply['status'] = 'ok';
					$reply['msg'] = "Your password has been updated.";
				} else $reply['error'] = "Update password failed.";
			}
			print json_encode($reply);

		} else if($_REQUEST['action'] == "gettasks"){
			$reply = array('status' => 'no');
			$result = getTasks();

			if ($result) {
				while ($row = pg_fetch_array($result)) {
					$reply['tasks'][]=$row;
				}
				$reply['status'] = 'ok';
			}
			print json_encode($reply);

		} else if($_REQUEST['action'] == "undo") {
			$reply = array('status' => 'ok');
			undoTask();
			print json_encode($reply);

		} else if($_REQUEST['action'] == "doit") {
			$reply = array('status' => 'ok');
			doTask();
			print json_encode($reply);

		} else if($_REQUEST['action'] == "delete") {
			$reply = array('status' => 'ok');
			deleteTask();
			print json_encode($reply);

		} else if($_REQUEST['action'] == "markdone") {
			$reply = array('status' => 'ok');
			doneTask();
			print json_encode($reply);

		} else if($_REQUEST['action'] == "addtask") {
			$reply = array('status' => 'ok');
			addTask();
			print json_encode($reply);

		} else if ($_REQUEST['action']=="getinfo"){
			getTaskInfo($_REQUEST['taskid'], $dbconn);

		} else if ($_REQUEST['action']=="edittask"){
			updateTask();
			$reply = array('status' => 'ok');
			print json_encode($reply);
		} else if($_REQUEST['action'] == "logout"){
			unset($_SESSION['user']);
			$reply = array('status' => 'ok');

			print json_encode($reply);
		}
	}

	
?>