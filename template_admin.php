<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

function display_title($title, $tag = 'h2', $class = 'page-title') {
	$ret = "<$tag class='$class'>";
	if (is_array($title)) { $ret .= implode(' › ', $title); }
	else { $ret .= $title; }
	$ret .= "</$tag>";
	return $ret;
}

function create_header($title = null) {
	global $path, $pageTitle;
	if (is_array($pageTitle)) { $title = implode(' › ', array_map('strip_tags', $pageTitle)); }
	else { $title = h($pageTitle); }
?>
<!DOCTYPE html>
<html lang="en">
<div id="loading">
	<img id="loading-image" src="/images/ajax-loader.gif" alt="Loading..."/>
</div>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?=SITE_TITLE?> Admin<?= blank($title) ? "" : " &mdash; $title" ?></title>
	<link href="<?=BASE_PATH?>css/admin.css" rel="stylesheet">
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<?php if (defined('DEV')): ?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="<?=BASE_PATH?>css/jquery.timepicker.min.css">
    <link rel="stylesheet" href="<?=BASE_PATH?>css/intltelinput.css">
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
	<!--<script src="<?=BASE_PATH?>js/jquery.js"></script>-->
	<script src="<?=BASE_PATH?>js/chosen.js"></script>
	<script src="<?=BASE_PATH?>js/chosen-ajax.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-transition.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-modal.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-alert.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-tooltip.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-typeahead.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-popover.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-tab.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-dropdown.js"></script>
	<script src="<?=BASE_PATH?>ckeditor/ckeditor.js"></script>
	<script src="<?=BASE_PATH?>ckeditor/adapters/jquery.js"></script>
    <script src="<?=BASE_PATH?>js/jquery.timepicker.min.js"></script>
    <script src="<?=BASE_PATH?>js/phonepicker.js"></script>
    <script src="<?=BASE_PATH?>js/admin.js"></script>
<?php else: ?>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="<?=BASE_PATH?>css/jquery.timepicker.min.css">
    <link rel="stylesheet" href="<?=BASE_PATH?>css/intltelinput.css">
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
	<!--<script src="<?=BASE_PATH?>js/jquery.js"></script>-->
	<script src="<?=BASE_PATH?>js/chosen.js"></script>
	<script src="<?=BASE_PATH?>js/chosen-ajax.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-transition.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-modal.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-alert.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-tooltip.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-typeahead.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-popover.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-tab.js"></script>
	<script src="<?=BASE_PATH?>js/bootstrap/bootstrap-dropdown.js"></script>
	<script src="<?=BASE_PATH?>ckeditor/ckeditor.js"></script>
	<script src="<?=BASE_PATH?>ckeditor/adapters/jquery.js"></script>
    <script src="<?=BASE_PATH?>js/jquery.timepicker.min.js"></script>
    <script src="<?=BASE_PATH?>js/phonepicker.js"></script>
    <script src="<?=BASE_PATH?>js/admin.min.js?<?= filemtime(dirname(__FILE__).'/js/admin.min.js') ?>"></script>
<?php endif ?>
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
</head>

<body>
	<div class="navbar navbar-static-top navbar-inverse">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="brand" href="<?=BASE_PATH?>"><img src="<?=BASE_PATH?>img/brand.png" alt="BCIU" /> Administrative Database v1.0</a>
				<?php if (is_logged()): ?>
				<ul class="nav pull-right">
					<li><a href="<?=BASE_PATH?>admin/logout">Logout</a></li>
				</ul>
				<?php endif ?>
				<ul class="nav">
					<?php if (has_permission('users_view')): ?><li <?=active_if($path[1]==='people')?>><a href="<?=BASE_PATH?>admin/people">People</a></li><?php endif ?>
                    <?php if (has_permission('organizations_view')): ?><li <?=active_if($path[1]==='organizations')?>><a href="<?=BASE_PATH?>admin/organizations">Organizations</a></li><?php endif ?>
					<?php if (has_permission('frameproduct_view')): ?><li <?=active_if($path[1]==='frameproducts')?>><a href="<?=BASE_PATH?>admin/frameproducts">Frame Products</a></li><?php endif ?>
					<?php if (has_permission('frameoption_view')): ?><li <?=active_if($path[1]==='frameoptions')?>><a href="<?=BASE_PATH?>admin/frameoptions">Frame Options</a></li><?php endif ?>
					<?php if (has_permission('order_view')): ?><li <?=active_if($path[1]==='orders')?>><a href="<?=BASE_PATH?>admin/orders">Orders</a></li><?php endif ?>
					<?php if (has_permission('pages_view')): ?><li <?=active_if($path[1]==='pages')?>><a href="<?=BASE_PATH?>admin/pages">CMS</a></li><?php endif ?>
					<?php if (has_permission('projects_view')): ?><li <?=active_if($path[1]==='projects')?>><a href="<?=BASE_PATH?>admin/projects">Projects</a></li><?php endif ?>
					<?php if (has_permission('vendors_view')): ?><li <?=active_if($path[1]==='vendors')?>><a href="<?=BASE_PATH?>admin/vendors">Vendors</a></li><?php endif ?>
					<?php if (has_permission('settings')): ?><li <?=active_if($path[1]==='settings')?>><a href="<?=BASE_PATH?>admin/settings">Settings</a></li><?php endif ?>
				</ul>
			</div>
		</div>
	</div>
	<div id="content">
		<div class="container-fluid">
			<div id="main">
<?php
	if ($GLOBALS['__messages_shown'] !== true) { echo status_messages(); }
}
	
function create_footer() {
?>
			</div>
		</div>
	</div>
</body>
<script language="javascript" type="text/javascript">
	$(window).load(function() {
		$('#loading').hide();
	});
</script>
</html>
<?php
}
