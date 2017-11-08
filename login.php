<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

if (is_logged() && $path[0] == "admin" && user_type() > User::OFFICIAL) { redirect_to(admin_url()); }
else if (is_logged() && $path[0] == "admin") { redirect_to('/'); }

if ($_POST['save']) {
	list($id, $type, $password) = sqll("SELECT id, type_id, password FROM users WHERE username = %s OR email = %s", unslash($_POST['username']), unslash($_POST['username']));
	if (crypt(unslash($_POST['password']), $password) === $password) {
		if (intval($type) === 0) {
			$error_message[] = 'Sorry, your account has been disabled.';
			if($path[0] == "admin") { redirect_to('/'); }
		}
		else {
			$_SESSION['uid'] = intval($id);
			if ($_POST['return'] && $path[0] == "admin") { redirect_to($_POST['return']); }
			else { if($path[0] == "admin" && user_type() > User::OFFICIAL) { redirect_to( admin_url()); }
			else if ($path[0] == "admin" && user_type() <= User::OFFICIAL) { redirect_to('/'); }  
			}
			success_message('You are now logged in.');
		}
	}
	else {
		error_message('The username and password combination you entered was invalid. Please try again.');
	}
}
$pageTitle = 'Login';

if (isset($_SESSION['uid']) && $path[0] != "admin")
{
	echo status_messages();
	echo '<center><a href="/logout" class="btn btn-primary">Log Out</a></center>';
}
else
{
	$form = new FormHelper();
	$form->prefix = 'login_';
	echo $form->start(),
		"<h2 class='page-title'>Login</h2>",
		status_messages(),
		$_GET['return'] ? $form->hidden('return', unslash($_GET['return'])) : '',
		$form->text('username', array('class' => 'input-large', 'label' => 'Username or E-mail')),
		$form->password('password', array('class' => 'input-large')),
		$form->hidden('loginprompt', 1),
		$form->submit('Login'),
		$form->end();
		
	echo '<br /><a href="/forgotpassword" style="color: #ffffff;" >Forgot Password?</a>';
}
