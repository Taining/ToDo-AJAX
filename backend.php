<?php
session_save_path("sess");
session_start();

header('Content-Type: application/json');

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
	}
}

function connectToDatabase($db_name, $db_user, $db_password){
        $dbconn = pg_connect("host=localhost port=5432 dbname=$db_name user=$db_user password=$db_password");
        if(!$dbconn){
            echo "Aw, Snap!";
            exit;      
        }

        return $dbconn; 
    }

?>