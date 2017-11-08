<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

function create_header($title = null) {
	global $path, $admin_menu;
	$page_id = blank($path[0]) ? 'home' : 'page';
	
	if ($_POST['submit'] == "SUBMIT") {
	$p = $_POST;
	
	$url = 'https://www.google.com/recaptcha/api/siteverify';		
	$privatekey = "6LfZDyIUAAAAAF53ZfqxGaJYCwNRyVtO8mbLin7W";
		
    $response = file_get_contents($url."?secret=".$privatekey."&response=".$p['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
	$data = json_decode($response);
	
	$name = $p['name'];
	$company = $p['company'];
	$email = $p['email'];
	$phone = $p['phone'];
	$comments = $p['comments'];
	
    
	if (empty($p['name']) || empty($p['email']) || empty($p['phone']))
	{
		$theerror .= "The following fields are required: Name, Phone, E-Mail."; 
		error_message($theerror);		
	}
	else if(isset($data->success) AND $data->success==true)
	{	
		//$to = "info@pioneerindustries.com";
		$to = "kenh@dzineit.net";
		$subject = SITE_TITLE." Contact Us";
		$content = "The following user has sent you a message through the contact form:
		Your Name: ".$name."
		Company Name: ".$company."
		Phone: ".$phone."
		E-mail: ".$email."
		Brief Message:
		".$comments;
		
		mail($to, $subject, $content);
		$theerror .= "We have received your inquiry and will be in touch shortly."; 
		
		unset($name);
		unset($company);
		unset($email);
		unset($phone);
		unset($comments);
	}
	else{
		$theerror .= "Invalid reCaptcha, please try again so we know you are not a spam bot."; 
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?=SITE_TITLE?><?php if (!empty($title)) echo ' &mdash; '.htmlspecialchars($title) ?></title>

	<?php if (DEV == true) { ?>
	<link rel="stylesheet" type="text/css" href="<?=BASE_PATH?>css/mainsite.css" />
	<?php } else { ?>
	<link rel="stylesheet" type="text/css" href="<?=BASE_PATH?>css/mainsite.min.css" />
	<?php } ?>
	
	<link rel="stylesheet" href="<?=BASE_PATH?>css/intltelinput.css">
	<link href="https://fonts.googleapis.com/css?family=Raleway:300,300i,400,700,800,900" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Cormorant+SC:300,400" rel="stylesheet">
	<!-- SET: SCRIPTS -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAXlFH_qIKgIPppT7oO-9tBcqa4ReD94k4" type="text/javascript"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
	<script src="/js/circle-progress.js"></script>
	
	<script src="<?=BASE_PATH?>js/phonepicker.js"></script>
	<?php if (DEV == true) { ?>
	<script src="<?=BASE_PATH?>js/mainsite.js?<?= filemtime(dirname(__FILE__).'/js/mainsite.js') ?>"></script>
	<?php } else { ?>
	<script src="<?=BASE_PATH?>js/mainsite.js?<?= filemtime(dirname(__FILE__).'/js/mainsite.min.js') ?>"></script>
	<?php } ?>
	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<style type="text/css">
		#loading {
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
		position: fixed;
		display: block;
		opacity: .6;
		background-color: #fff;
		z-index: 99;
		text-align: center;
		}
		#loading-image {
		position: absolute;
		top: 40%;
		left: 50%;
		margin-left: -50px;
		z-index: 600;
		}
	</style>
	<script type="text/javascript">
		$(document).ready(function() {
        if("<?php echo $_SESSION['timezoneoffset']; ?>".length==0){
				var visitortime = new Date();
				var visitortimezoneoffset = -visitortime.getTimezoneOffset()*60;
				var dataString = 'time='+visitortimezoneoffset;
				$.ajax({
					type: "POST",
					url: "/sec_ajax/settimezone",
					data: dataString,
					success: function(){
						location.reload();
					}
				});
			}
		});
	</script>
	

<!-- END: SCRIPTS -->

<?php if (DEV == false)
{
	?>
	<!-- [[ INSERT GOOGLE ANALYTICS HERE!!! ]] -->
	<?php
} ?>
</head>

<body id="<?= h($page_id) ?>">
<div id="loading">
	<img id="loading-image" src="/images/ajax-loader.gif" alt="Loading..."/>
</div>

	<div class="topbar">
		<div class="topleft">
			<div class="menuexpand">
				<div class="mainmenu">
					<ul>
						<li><a href="/">Home</a></li>
						<li><a href="/about-pioneer">About Pioneer</a></li>
						<li><a href="/product">Products</a></li>
						<li><a href="/projects">Projects</a></li>
						<li><a href="/distributers">Distributors</a></li>
						<li><a href="/resources">Resources</a></li>
						<li><a href="/customer-area">Customer Portal</a></li>
						<li><a href="/contact-us">Contact Us</a></li>
					</ul>
				</div>
			</div><div class="mainlogo"><a href="/"></a></div>
		</div>
	</div>
		
		<?php if ($GLOBALS['__messages_shown'] !== true) { echo '<div class="margins">'.status_messages().'</div>'; } ?>
        <!-- maincontent ends -->
		<?php
}
		function create_footer() {
			global $path;
		?>
	<div class="footermain">
		<div class="landingmargins">
			<div class="left">
				<a class="footerlogo" href="/">&nbsp;</a><br>
				<p>© Pioneer Industries All Rights Reserved</p>
			</div>
			<div class="right">
				<div class="partnerlogos"><a class="sdilogo" href="#">&nbsp;</a> <a class="naammlogo" href="#">&nbsp;</a></div>
				<div class="footernav">
					<ul>
						<li><a href="#">SITE MAP</a></li>
						<li><a href="#">CUSTOMER AREA</a></li>
						<li><a href="/contact-us">CONTACT US</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</body>
<script language="javascript" type="text/javascript">
	$(window).load(function() {		
		$('#loading').hide();
	});
	
	function showLoad() {
		$('#loading').show();
	}
</script>
</html>
<?php
}
