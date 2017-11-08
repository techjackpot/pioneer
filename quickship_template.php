<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

function create_header($title = null) {
	global $path, $admin_menu;
	$page_id = blank($path[0]) ? 'home' : 'page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?=SITE_TITLE?><?php if (!empty($title)) echo ' &mdash; '.htmlspecialchars($title) ?></title>

	<?php if (DEV == true) { ?>
	<link rel="stylesheet" type="text/css" href="<?=BASE_PATH?>css/front.css" />
	<?php } else { ?>
	<link rel="stylesheet" type="text/css" href="<?=BASE_PATH?>css/front.min.css" />
	<?php } ?>
	
	<link rel="stylesheet" href="<?=BASE_PATH?>css/intltelinput.css">
	<link href='https://fonts.googleapis.com/css?family=Roboto:400,400italic,100,100italic,300,300italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
	<!-- SET: SCRIPTS -->
	<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.js"></script>-->
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
	<script src="/js/circle-progress.js"></script>
	
	<script src="<?=BASE_PATH?>js/phonepicker.js"></script>
	<?php if (DEV == true) { ?>
	<script src="<?=BASE_PATH?>js/front.js?<?= filemtime(dirname(__FILE__).'/js/front.js') ?>"></script>
	<?php } else { ?>
	<script src="<?=BASE_PATH?>js/front.js?<?= filemtime(dirname(__FILE__).'/js/front.min.js') ?>"></script>
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

    <!-- wrapper starts -->
    <div class="wrapper<?=(is_home() ? "" : "2");?>">
        
         <!-- Header Starts -->
         
        <div class="header">
			<div class="headerbgtop">
				<div class="margins">
					<div class="padding">
						<a href="<?=(is_logged()? '/guidelines' : '/')?>">
							<img src="/images/logo.png" border="0" class="mainlogo" />
						</a>
						<?php if (is_logged()) { ?>
						<div class="togglemenu">
							<div class="dropmenu">
								<ul>
									<li><a href="/orderhistory">Order History</a></li>
									<li><a href="/logout">Log Out</a></li>
								</ul>
							</div>
						</div>
						<?php } ?>
						<img src="/images/quickship-truck.png" class="qstruckimg" />
						<div class="clear"></div>
					</div>
				</div>
			</div>
        </div> 
        <!-- Header ends -->
            
        <!-- maincontent Starts -->
		<div class="main">
		
		<?php if ($GLOBALS['__messages_shown'] !== true) { echo '<div class="margins">'.status_messages().'</div>'; } ?>
        <!-- maincontent ends -->
		<?php
}
		function create_footer() {
			global $path;
		?>
			<div class="clear"></div>
		</div>
		<!-- footer starts -->
		<div class="footer">
			<div class="margins">
				<div class="padding">
					<div class="left">
						Copyright <?=date('Y')?> Pioneer Industries
					</div>
					<div class="right">
						<?php if (is_logged()) { ?><a href="/logout">Log Out</a> &nbsp;-&nbsp; <?php } ?><a href="http://pioneerindustries.com/contact-us">Contact Us</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- wrapper ends -->
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
