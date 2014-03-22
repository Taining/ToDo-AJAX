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






	} else if($_REQUEST['action'] == "gettasks"){
		$reply=array();
		$dbconn = connectToDatabase($db_name, $db_user, $db_password);
		
		$get_tasks_query="SELECT * FROM tasks WHERE uid=$1 ORDER BY taskid";
		$result = pg_prepare($dbconn, "get_tasks", $get_tasks_query);
		$result = pg_execute($dbconn, "get_tasks", array($_SESSION['user']));
		
		while ($row = pg_fetch_array($result)) {
			/*
			$taskid = $row['taskid'];
			$dscrp = $row['dscrp'];
			$total = $row['total'];
			$progress = $row['progress'];
			$createtime = $row['createtime'];
			*/
			$reply['tasks'][]=$row;
		}
		print json_encode($reply);
		
	} else if($_REQUEST['action'] == "undo") {
		$reply=array();
		$dbconn = connectToDatabase($db_name, $db_user, $db_password);
		
		$get_progress_query = "SELECT progress FROM tasks WHERE taskid=$1";
		$result = pg_prepare($dbconn, "get_progress", $get_progress_query);
		$result = pg_execute($dbconn, "get_progress", array($_REQUEST['taskid']));

		$row = pg_fetch_array($result);
		$progress = $row['progress'];
		$progress = $progress - 1;
	
		$update_progress_query = "UPDATE tasks SET progress = $1 WHERE taskid=$2";
		$result = pg_prepare($dbconn, "update_progress", $update_progress_query);
		$result = pg_execute($dbconn, "update_progress", array($progress, $_REQUEST['taskid']));
		
		$reply['status'] = "ok";
		print json_encode($reply);
		
	} else if($_REQUEST['action'] == "doit") {
		$reply=array();
		$dbconn = connectToDatabase($db_name, $db_user, $db_password);
		
		$get_progress_query = "SELECT progress, uid FROM tasks WHERE taskid=$1";
		$result = pg_prepare($dbconn, "get_progress", $get_progress_query);
		$result = pg_execute($dbconn, "get_progress", array($_REQUEST['taskid']));
		$row = pg_fetch_row($result);
		
		// progress++
		$progress = $row[0];
		$progress += 1;
		$update_query = "UPDATE tasks SET progress=$1 WHERE taskid=$2";
		$result = pg_prepare($dbconn, "update_progress", $update_query);
		$result = pg_execute($dbconn, "update_progress", array($progress, $_REQUEST['taskid']));
		
		// done++
		$uid = $row[1];
		$get_done_query = "SELECT done FROM appuser WHERE uid=$1";
		$result = pg_prepare($dbconn, "get_done", $get_done_query);
		$result = pg_execute($dbconn, "get_done", array($_SESSION['user']));
		
		$row = pg_fetch_row($result);
		$done = $row[0];
		$done += 1;
		$update_query = "UPDATE appuser SET done=$1 WHERE uid=$2";
		$result = pg_prepare($dbconn, "update_done", $update_query);
		$result = pg_execute($dbconn, "update_done", array($done, $_SESSION['user']));
		
		$reply['status'] = "ok";
		print json_encode($reply);
		
	} else if($_REQUEST['action'] == "delete") {
		$reply=array();	
		$dbconn = connectToDatabase($db_name, $db_user, $db_password);
		
		$delete_task_query = "DELETE FROM tasks WHERE taskid=$1;";
		$delete_result = pg_prepare($dbconn, "delete_task", $delete_task_query);
		$delete_result = pg_execute($dbconn, "delete_task", array($_REQUEST['taskid']));
		
		$reply['status'] = "ok";
		print json_encode($reply);
		
	} else if($_REQUEST['action'] == "markdone") {
		$reply=array();	
		$dbconn = connectToDatabase($db_name, $db_user, $db_password);
		
		$total_query = "SELECT total FROM tasks WHERE uid=$1 AND taskid=$2";
		$total_result = pg_prepare($dbconn, "get_total", $total_query);
		$total_result = pg_execute($dbconn, "get_total", array($_SESSION['user'], $_REQUEST['taskid']));
		$row = pg_fetch_array($total_result);

		$done_task_query = "UPDATE tasks SET progress = $1 WHERE taskid = $2;";
		$done_result = pg_prepare($dbconn, "done_task", $done_task_query);
		$done_result = pg_execute($dbconn, "done_task", array($row['total'], $_REQUEST['taskid']));
		
		$reply['status'] = "ok";
		print json_encode($reply);
		
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