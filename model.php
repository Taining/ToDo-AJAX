<?php

//user model---------------------------------------------------
function findUser(){

}

function addUser(){

}

function updateUser(){

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