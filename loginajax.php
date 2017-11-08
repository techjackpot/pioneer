<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

$no_template = true;

if ($_POST['save']) {
	list($id, $type, $password) = sqll("SELECT id, type_id, password FROM users WHERE username = %s OR email = %s", unslash($_POST['username']), unslash($_POST['username']));
	if (crypt(unslash($_POST['password']), $password) === $password) {
		if (intval($type) === 0) {
			echo 'Your account has been disabled. Please contact us.';
		}
		else {
			$_SESSION['uid'] = intval($id);
			success_message('You are now logged in.');
			echo "1";
		}
	}
	else {
		echo 'The username and password combination you entered was invalid. Please try again.';
	}
}