<?php
	include "config.inc";
	include "model.php";
	
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
	
	$dbconn = connectToDatabase($db_name, $db_user, $db_password);
	
	if ($_REQUEST['action']=="getinfo"){
		getTaskInfo($_REQUEST['taskid'], $dbconn);
	}
	
	if ($_REQUEST['action']=="edittask"){
		updateTask($_REQUEST['taskid'],$_REQUEST['dscrp'],$_REQUEST['details'],$_REQUEST['total'],$dbconn);
	}
?>