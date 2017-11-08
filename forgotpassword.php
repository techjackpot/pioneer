<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

if ($_POST['save']) {
	if ($path[1])
	{
		list($id, $type, $email) = sqll("SELECT id, type_id, email FROM users WHERE forgotpasswordkey = %s", $path[1]);
		if (!empty($id)) {
			
			if (unslash($_POST['password1']) != unslash($_POST['password2']))
			{
				error_message('Your passwords do not match. Please try again.');
			}
			else if (is_array(check_password($_POST['password1'])))
			{
				$errors = check_password($_POST['password1']);
				
				foreach ($errors as $error)
				$error_string .= $error."<br />";
				
				error_message('There were errors with your password:<br />'.$error_string);
			}
			else{
				$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
				$newpass = crypt($_POST['password1'], '$2y$10$'.$salt.'$');
				sql("UPDATE users SET password = %s, forgotpasswordkey = null WHERE id = %s",$newpass,$id);
				success_message('Your password has been successfully changed.');
				redirect_to('/login');
			}
		}
	} else {
		list($id, $type, $email) = sqll("SELECT id, type_id, email FROM users WHERE email = %s", unslash($_POST['email']));
		if (!empty($id)) {
			if (intval($type) === 0) {
				$error_message[] = 'Sorry, your account has been disabled. Please contact us';
			}
			else {
				$user = User::get($id);
				
				$forgotpasswordkey = bin2hex(openssl_random_pseudo_bytes(rand(2,10)));
				sql("UPDATE users SET forgotpasswordkey = %s WHERE id = %s",$forgotpasswordkey,$id);
				
				$mail = new Mail();
				$mail->to = $user;
				$mail->subject = SITEID." Forgot Password";
				$mail->content = "<p>You or someone has requested to reset your password on ".SITE_URL.". Please click the link below to reset your password:</p>
					<p>".SITE_URL."/forgotpassword/".$forgotpasswordkey."</p>";
				
				$mail->send();
				
				success_message('We have emailed you a link to reset your password. Please check your e-mail.');
			}
		}
		else {
			error_message('An account with that e-mail does not appear to exist. Please try again.');
		}
	}
}
$pageTitle = 'Forgot Password';

?><div class="margins">
    <div class="padding">
        <div class="qscontainer redstripbottom">
            <div class="largebanner banner1">&nbsp;</div>
            <div class="qshomecontent">
            
                    <?php
if ($path[1])
{
	list($id, $type, $email) = sqll("SELECT id, type_id, email FROM users WHERE forgotpasswordkey = %s", $path[1]);
	
	if (!empty($id)) {
	$form = new FormHelper();
	$form->prefix = 'forgotpass_';
	echo $form->start(),
	"<h1>Change Your Password</h1>",
	status_messages(),
	$_GET['return'] ? $form->hidden('return', unslash($_GET['return'])) : '',
	$form->password('password1', array('class' => 'input-large', 'label' => 'Please enter a secure password.')),
	'<div class="clear"></div>',
	$form->password('password2', array('class' => 'input-large', 'label' => 'Please re-enter your password.')),
	'<div class="clear"></div>',
	$form->submit('Submit'),
	$form->end();
	}
	else {
		echo "<h1>Change Your Password</h1>
		<p>Your password link is either invalid or has expired. Please try again.";
	}
	
} else {
$form = new FormHelper();
$form->prefix = 'forgotpass_';
echo $form->start(),
	"<h1>Forgot Password</h1>",
	status_messages(),
	$_GET['return'] ? $form->hidden('return', unslash($_GET['return'])) : '',
	$form->text('email', array('class' => 'input-large', 'label' => 'Please enter your e-mail address')),
	'<div class="clear"></div>',
	$form->submit('Submit'),
	$form->end();
}
?><div class="clear"></div>
                  </div>
        </div>
    </div>
</div>
<?php

 ?>
