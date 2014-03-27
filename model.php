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
function findUser(){
	//find a match in database
	$dbconn = connectToDatabase(db_name, db_user, db_password);
	$select_user_query = ("SELECT * FROM appuser WHERE email = $1 AND password = $2;");
	$result = pg_prepare($dbconn, "select_user", $select_user_query);
	$result = pg_execute($dbconn, "select_user", array($_REQUEST['email'], md5($_REQUEST['password'])));

	if(pg_num_rows($result)){
		$row = pg_fetch_array($result);
		$_SESSION['user'] = $row['uid'];
		return true;
	}

	return false;
}

function addUser(){
	$dbconn = connectToDatabase(db_name, db_user, db_password);
	$select_user_query = ("SELECT * FROM appuser WHERE email = $1;");
	$result = pg_prepare($dbconn, "select_user", $select_user_query);
	$result = pg_execute($dbconn, "select_user", array($_REQUEST['email']));

	if(pg_num_rows($result)){
		return false;
	} else {
		//insert into database
		$year = $_REQUEST['year'];
		$month = $_REQUEST['month'];
		$day = $_REQUEST['day'];
		$insert_user_query = "INSERT INTO appuser (email, fname, lname, password, birthday, signupdate, news, sex, done) VALUES($1, $2, $3, $4, $5, $6, $7, $8, 0);";
		$result = pg_prepare($dbconn, "insert_user", $insert_user_query);
		$result = pg_execute($dbconn, "insert_user", array($_REQUEST['email'], $_REQUEST['fname'], $_REQUEST['lname'], md5($_REQUEST['password']), "$year-$month-$day", date("Y-m-d"), $_REQUEST['news'], $_REQUEST['sex']));

		return true;
	}
}

function getUserInfo(){
	$dbconn = connectToDatabase(db_name, db_user, db_password);
	$result = pg_query($dbconn, "SELECT * FROM appuser WHERE uid = $_SESSION[user]");
	$row = pg_fetch_array($result);

	return $row;
}

function updateUser(){
	$dbconn = connectToDatabase(db_name, db_user, db_password);
	//update user account information
	$birthday = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'];
	$update_user_query = "UPDATE appuser SET (email, fname, lname, birthday, news, sex) = ($1, $2, $3, $4, $5, $6) WHERE uid = $7;";
	$result = pg_prepare($dbconn, "update_user", $update_user_query);
	$result = pg_execute($dbconn, "update_user", array($_REQUEST['email'], $_REQUEST['fname'], $_REQUEST['lname'], $birthday, $_REQUEST['news'], $_REQUEST['sex'], $_SESSION['user']));

	if ($result) return true;
	else return false;
}

function updatePassword(){
	
}



//task model---------------------------------------------------
function getTasks(){

}

function addTask(){

}

function deleteTask(){

}

function updateTask($taskid,$dscrp,$details,$total,$dbconn){
	$reply=array();
	
	// get current progress
	$get_progress_query = "SELECT progress FROM tasks WHERE taskid=$1";
	$progress_result = pg_prepare($dbconn, "get_progress", $get_progress_query);
	$progress_result = pg_execute($dbconn, "get_progress", array($taskid));
	if($progress_result) {
		$row = pg_fetch_row($progress_result);
		$progress= $row[0];
	}
	
	// if new total time is less than progress, we assume that the task has been finished
	if($progress > $total) {
		$progress = $total;
	}	
	
	//update
	$update_query = "UPDATE tasks SET dscrp=$1, details=$2, total=$3, progress=$4 WHERE taskid=$5;";
	$result = pg_prepare($dbconn, "update_query", $update_query);
	$result = pg_execute($dbconn, "update_query", array($dscrp, $details, $total, $progress, $taskid));
	
	$reply['status']="ok";
	print json_encode($reply);
}

function doTask(){

}

function undoTask(){

}

function doneTask(){

}

function getTaskInfo($taskid, $dbconn){
	$reply=array();
	
	$query="SELECT * FROM tasks WHERE taskid=$1";
	$result = pg_prepare($dbconn, "get_task_info", $query);
	$result = pg_execute($dbconn, "get_task_info", array($taskid));
	
	$row = pg_fetch_array($result);
	$reply=$row;
	$reply['status']='ok';
	
	print json_encode($reply);
}

?>