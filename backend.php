<?php
session_save_path("sess");
session_start();

header('Content-Type: application/json');

if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action'] == 'auth') {
		$reply = array();
		if (isset($_SESSION['authenticated'])) {
			$reply['auth'] = 'yes';
		} else {
			$reply['auth'] = 'no';
		}

		print json_encode($reply);
	} elseif ($_REQUEST['action'] == 'login') {
		$reply = array();
		if (!$_REQUEST['email'] || !$_REQUEST['password']) {
			$reply['status'] = 'no';
			$reply['error'] = 'Invalide user or password.';
		}
	}
}

?>