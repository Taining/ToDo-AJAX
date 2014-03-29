<?php

require "config.inc";

function connectToDatabase($db_name, $db_user, $db_password){
	$dbconn = pg_connect("host=localhost port=5432 dbname=$db_name user=$db_user password=$db_password");
	if(!$dbconn){
		echo "OMG";
	}

	return $dbconn; 
}

//user model---------------------------------------------------
function findUser($dbconn){
	$select_user_query = ("SELECT * FROM appuser WHERE email = $1;");
	$result = pg_prepare($dbconn, "select_user", $select_user_query);
	$result = pg_execute($dbconn, "select_user", array($_REQUEST['email']));
	
	//calculate password
	$row = pg_fetch_array($result);
	$password = hash("sha256", $_REQUEST['password'] . $row['salt']);

	if($row['password'] == $password){
		$_SESSION['user'] = $row['uid'];
		return true;
	}

	return false;
}

function addUser($dbconn){
	$select_user_query = ("SELECT * FROM appuser WHERE email = $1;");
	$result = pg_prepare($dbconn, "select_user", $select_user_query);
	$result = pg_execute($dbconn, "select_user", array($_REQUEST['email']));

	if(pg_num_rows($result)){
		return false;
	} else {
		//HASH FUNCTION USED: append salt at end of password, hash it using sha256
		//SALT USED: generate a unique id with random number; compute its md5 hash value; get first 8 chars of hashed string as salt
		$intermediateSalt = md5(uniqid(rand(), true));
    	$salt = substr($intermediateSalt, 0, 8);
    	$password = hash("sha256", $_REQUEST['password'] . $salt);
    
		//insert into database
		$year = $_REQUEST['year'];
		$month = $_REQUEST['month'];
		$day = $_REQUEST['day'];
		$insert_user_query = "INSERT INTO appuser (email, fname, lname, password, salt, birthday, signupdate, news, sex, done) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, 0);";
		$result = pg_prepare($dbconn, "insert_user", $insert_user_query);
		$result = pg_execute($dbconn, "insert_user", array($_REQUEST['email'], $_REQUEST['fname'], $_REQUEST['lname'], $password, $salt, "$year-$month-$day", date("Y-m-d"), $_REQUEST['news'], $_REQUEST['sex']));

		return true;
	}
}

function getUserInfo($dbconn){
	$result = pg_query($dbconn, "SELECT * FROM appuser WHERE uid = $_SESSION[user]");
	$row = pg_fetch_array($result);

	return $row;
}

function updateUser($dbconn){
	//update user account information
	$birthday = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'];
	$update_user_query = "UPDATE appuser SET (email, fname, lname, birthday, news, sex) = ($1, $2, $3, $4, $5, $6) WHERE uid = $7;";
	$result = pg_prepare($dbconn, "update_user", $update_user_query);
	$result = pg_execute($dbconn, "update_user", array($_REQUEST['email'], $_REQUEST['fname'], $_REQUEST['lname'], $birthday, $_REQUEST['news'], $_REQUEST['sex'], $_SESSION['user']));

	if ($result) return true;
	else return false;
}

function updatePassword($dbconn){
	//calculate password
	$intermediateSalt = md5(uniqid(rand(), true));
    $salt = substr($intermediateSalt, 0, 8);
    $password = hash("sha256", $_REQUEST['newPassword'] . $salt);

	$update_pwd_query = "UPDATE appuser SET (password, salt) = ($1, $2) WHERE uid = $3;";
	$result = pg_prepare($dbconn, "update_pwd", $update_pwd_query);
	$result = pg_execute($dbconn, "update_pwd", array($password, $salt, $_SESSION['user']));

	if($result) return true;
	else return false;
}



//task model---------------------------------------------------
function getTasks($dbconn){
	$get_tasks_query="SELECT * FROM tasks WHERE uid=$1 ORDER BY taskid";
	$result = pg_prepare($dbconn, "get_tasks", $get_tasks_query);
	$result = pg_execute($dbconn, "get_tasks", array($_SESSION['user']));

	return $result;
}

function addTask($dbconn){
	// get new taskid
	$query = "SELECT MAX(taskid) FROM tasks;";
	$result=pg_query($dbconn, $query);
	$row = pg_fetch_row($result);
	$taskid = $row[0] + 1;

	// assign ordering value to the new task
	$query = "SELECT COUNT(*) FROM tasks;";
	$result=pg_query($dbconn, $query);
	$row = pg_fetch_row($result);
	$ordering = $row[0] + 1;

	// add task
	$query = "INSERT INTO tasks(uid, taskid, dscrp, details, total, progress, ordering, createtime, priority) VALUES($1, $2, $3, $4, $5, 0, $6, $7, $8)";
	$result = pg_prepare($dbconn, "my_query", $query);
	$result = pg_execute($dbconn, "my_query", array($_SESSION['user'], $taskid, $_REQUEST['dscrp'], $_REQUEST['details'], $_REQUEST['total'], $ordering, date("Y-m-d"), $ordering));

	return true;
}

function deleteTask($dbconn){
	$delete_task_query = "DELETE FROM tasks WHERE taskid=$1;";
	$delete_result = pg_prepare($dbconn, "delete_task", $delete_task_query);
	$delete_result = pg_execute($dbconn, "delete_task", array($_REQUEST['taskid']));

	return true;
}

function updateTask($dbconn){
	// get current progress
	$get_progress_query = "SELECT progress FROM tasks WHERE taskid=$1";
	$progress_result = pg_prepare($dbconn, "get_progress", $get_progress_query);
	$progress_result = pg_execute($dbconn, "get_progress", array($_REQUEST['taskid']));
	if($progress_result) {
		$row = pg_fetch_row($progress_result);
		$progress= $row[0];
	}
	
	// if new total time is less than progress, we assume that the task has been finished
	if($progress > $_REQUEST['total']) {
		$progress = $_REQUEST['total'];
	}	
	
	//update
	$update_query = "UPDATE tasks SET dscrp=$1, details=$2, total=$3, progress=$4 WHERE taskid=$5;";
	$result = pg_prepare($dbconn, "update_query", $update_query);
	$result = pg_execute($dbconn, "update_query", array($_REQUEST['dscrp'], $_REQUEST['details'], $_REQUEST['total'], $progress, $_REQUEST['taskid']));
	
	return true;
}

function doTask($dbconn){
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

	return true;
}

function undoTask($dbconn){
	$get_progress_query = "SELECT progress FROM tasks WHERE taskid=$1";
	$result = pg_prepare($dbconn, "get_progress", $get_progress_query);
	$result = pg_execute($dbconn, "get_progress", array($_REQUEST['taskid']));

	$row = pg_fetch_array($result);
	$progress = $row['progress'];
	$progress = $progress - 1;
	
	$update_progress_query = "UPDATE tasks SET progress = $1 WHERE taskid=$2";
	$result = pg_prepare($dbconn, "update_progress", $update_progress_query);
	$result = pg_execute($dbconn, "update_progress", array($progress, $_REQUEST['taskid']));

	return true;
}

function doneTask($dbconn){
	$total_query = "SELECT total FROM tasks WHERE uid=$1 AND taskid=$2";
	$total_result = pg_prepare($dbconn, "get_total", $total_query);
	$total_result = pg_execute($dbconn, "get_total", array($_SESSION['user'], $_REQUEST['taskid']));
	$row = pg_fetch_array($total_result);

	$done_task_query = "UPDATE tasks SET progress = $1 WHERE taskid = $2;";
	$done_result = pg_prepare($dbconn, "done_task", $done_task_query);
	$done_result = pg_execute($dbconn, "done_task", array($row['total'], $_REQUEST['taskid']));

	return true;
}

function getTaskInfo($dbconn){
	$query="SELECT * FROM tasks WHERE taskid=$1";
	$result = pg_prepare($dbconn, "get_task_info", $query);
	$result = pg_execute($dbconn, "get_task_info", array($_REQUEST['taskid']));
	$row = pg_fetch_array($result);
	
	return $row;
}

function caculateRate($dbconn) {
	// caculate rate
	$signup_query = "SELECT signupdate, done FROM appuser WHERE uid=$1";
	$signup_result = pg_prepare($dbconn, "signup", $signup_query);
	$signup_result = pg_execute($dbconn, "signup", array($_SESSION['user']));

	$row = pg_fetch_array($signup_result);
	$signupdate = $row['signupdate'];
	$done = $row['done'];	// number of units that the user has done in total

	$signup = strtotime(date("M d Y", strtotime($signupdate)));
	$cur = strtotime(date("M d Y"));
	$dateDiff = ($cur - $signup)/3600/24;	// number of days since signup
	if ($dateDiff != 0) {
		$rate = intval($done / $dateDiff);
	} else {
		$rate = 0;
	}
	return $rate;
}

function caculateRemaining ($dbconn, $rate) {
	// caculate remaining days
	$query = "SELECT SUM(total), SUM(progress) FROM tasks WHERE uid=$1";
	$result = pg_prepare($dbconn, "total_progress", $query);
	$result = pg_execute($dbconn, "total_progress", array($_SESSION['user']));

	$row = pg_fetch_row($result);
	$total = $row[0];
	$progress = $row[1];
	$remaining = $total - $progress;
	
	if($rate != 0) {
		$remainingDays = ceil($remaining / $rate);
	}else{
		$remainingDays = "Infinite";
	}
	
	return $remainingDays;
}

?>