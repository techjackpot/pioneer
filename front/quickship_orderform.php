<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/?return='.$_SERVER['REQUEST_URI'] );

$no_template = true;

$model = Order::first("uniqueid = ".sanitize($path[1]));
$user = User::get($model['user_id']);

if ($model)
{
	$form = new FormHelper();
	$form->prefix = 'order_';
	$form->data = &$model;

	$table = new TableHelper('OrderLine');
	$params = $table->find_params();
	$params['where'] = '`order_id` = '.sanitize($model['id']);
	$params['sort'] = 'created_at ASC'; 
	$data = OrderLine::find( $params );
}
else
{
	error_message('No order found.');
	redirect_to( '/' );
}

function displaybanner($model, $user, $page, $pages, $displayextras = false)
{
	?><div class="header" <?php if ($displayextras == false) { ?>style="margin-top: 45px;"<?php } ?>>
		
    	<div style="float:left"><img style="width:280; height:38px;" src="/img/pion_log.png" /></div>
        
    	<div style="width:150px; height:28px; float:left;"><p style="padding-left:20px; font-weight:200; font-size:14px;">Tel: (201) 933-1900<br/> 
    	Fax: (201) 933-9580</p>
    	</div>
        
    	<div style="float:right;"><p style="float:right; font-size:17px">PURCHASE ORDER / NON-STOCKED ENGINEERED FRAME</p>
    	</div>
    
    </div>
    
    <div style="float: left; width: 100%; border-bottom: 1px solid;">
		<div style="float:right; width:200px;">
        	<p><span style="float:right;">Page: <?=$page?> of <?=$pages?></span></p>
        </div>
		<div style="float:right; width:200px;">
        	<p>Date: <?=h($model->display_dateprocessed());?></p>
        </div>
		<div style="float:right; width:190px;">
        	<p>Job# QS<?=h($model->display("id"))?></p>
        </div>
		<div style="float:right; width:350px;">
        	<p>Customer PO# <?=h($model->display("customer_po"));?></p>
        </div>	
    </div>
    
	<?php if ($displayextras == true) { ?>
    <div style="width:100%; float:left; height:134px;">
    	<div style="width:25%; float:left;">
        	<p style="padding-left:20px;">Sold To:</p>
            <p style="padding-left:20px; padding-top:2px;"><?=$model->billto()?></p>
       	</div>
        
        <div style="width:25%; float:left;">
        	<p style="padding-left:20px;">Ship To:</p>
            <p style="padding-left:20px; padding-top:2px;"><?=$model->shipto()?></p>
    	</div>
		<div style="width:30%; float:left;">
        	<p style="padding-left:20px;">Shipping Notes:</p>
            <p style="padding-left:20px; padding-top:2px;"><?=h($model->display("shipping_notes"))?></p>
    	</div>
       <div style="width:20%; float:right; font-size:11px;">
            <p style="padding-bottom:18px;">Customer Code: <?=h($user->display('customer_number'));?></p>
            <p>Shiping Code: <?=h($model->display("shipping_code"));?></p>
            <p></p>
    	</div>
        <br style="clear: left;" />
        
        <div>
        <p style="padding-left:20px; padding-top:10px;">Authorized By:&nbsp;&nbsp;(Signature)</p>
        <p style="padding-left:20px;">THIS IS AN OFICIAL ORDER <span style="font-family: 'Gochi Hand', cursive; text-decoration: underline; font-size: 18px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=h($model->display("first_name"));?> <?=h($model->display("last_name"));?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
        </div>
    </div><?php
	}
	else
	{
		?><div style="width:100%; float:left; height:30px;">&nbsp;</div><?php
	}
	
}

function displayheader($printshow = false) {
	return '<tr>
			<td colspan="2"><div class="label">Line</div></td>
			<td colspan="9"><div class="label">General Frame Information</div></td>
			<td colspan="3"><div class="label">Lock Information</div></td>
			<td><div class="label">Hand</div></td>
			<td colspan="4"><div class="label">Label / Assembly</div></td>
			<td colspan="3"><div class="label">Hinge</div></td>
			<td colspan="2"><div class="label">Other</div></td>
			<td><div class="label">&nbsp;</div></td>
			<td><div class="label">&nbsp;</div></td>
		</tr>
	<tr '.($printshow ? 'class="printshow"' : '').' >
	<td><div class="label">1</div>&nbsp;</td>
	<td><div class="label">2</div>&nbsp;</td>
	<td><div class="label">3</div>&nbsp;</td>
	<td><div class="label">4</div>&nbsp;</td>
	<td><div class="label">5</div>&nbsp;</td>
	<td><div class="label">6</div>&nbsp;</td>
	<td><div class="label">7</div>&nbsp;</td>
	<td><div class="label">8</div>&nbsp;</td>
	<td><div class="label">9</div>&nbsp;</td>
	<td><div class="label">10</div>&nbsp;</td>
	<td><div class="label">11</div>&nbsp;</td>
	<td><div class="label">12</div>&nbsp;</td>
	<td><div class="label">13</div>&nbsp;</td>
	<td><div class="label">14</div>&nbsp;</td>
	<td><div class="label">15</div>&nbsp;</td>
	<td><div class="label">16</div>&nbsp;</td>
	<td><div class="label">17</div>&nbsp;</td>
	<td><div class="label">18</div>&nbsp;</td>
	<td><div class="label">19</div>&nbsp;</td>
	<td><div class="label">20</div>&nbsp;</td>
	<td><div class="label">21</div>&nbsp;</td>
	<td><div class="label">22</div>&nbsp;</td>
	<td><div class="label">23</div>&nbsp;</td>
	<td><div class="label">24</div>&nbsp;</td>
	<td><div class="label">25</div>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	<tr style="height: 80px;" class="vertalign '.($printshow ? 'printshow' : '').'">
	<td><div class="label rotate2" style="width: 16px;">Line</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 22px;">Qty</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 25px;">Series</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 20px;">Gage</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 27px;">Material</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 24px;">Thk</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 24px;">Rabbet</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 35px;">Type</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 38px;">Jamb Depth</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 46px;">Width</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 46px;">Height</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 34px;">Strike</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 34px;">Strike<br>Location</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 34px;">2nd Strike</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 26px;">Hand</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 34px;">Profile #</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 22px;">Label</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 22px;">Weld KD</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 32px;">Anchor</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 33px;">Hinge</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 26px;">Hg Qty</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 26px;">Hinge<br>Location</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 16px;">Closer</div>&nbsp;</td>
	<td><div class="label rotate2" style="width: 16px;">Bolt</div>&nbsp;</td>
	<td><div class="label paddingtop2" style="width: 114px;">Additional</div>&nbsp;</td>
	<td><div class="label paddingtop2" style="width: 37px;">List</div>&nbsp;</td></tr>';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pioneer Quick Ship Order Form</title>
<link href="https://fonts.googleapis.com/css?family=Gochi+Hand" rel="stylesheet">
<style type="text/css">
	body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,p,blockquote,th,td { margin:0; padding:0;outline:none;}
table {	border-collapse:collapse; border-spacing:0;}
fieldset,img { border:0; }
address,caption,cite,code,dfn,em,strong,th,var {font-style:normal; font-weight:normal;}
ol,ul { list-style:none;}
caption,th {text-align:left;}
h1,h2,h3,h4,h5,h6 {	font-size:100%;	font-weight:bold;}
q:before,q:after {content:'';}
abbr,acronym { border:0;}
.clear{clear:both; font-size:0px;line-height:0px; display:block;}
.last	{margin:0 !important;}
.pad_last{padding:0 !important;}
.no_bg {background:none !important;}
.italic { font-style: italic !important; }
/**************************************************************************************/
a											{color:#000; text-decoration:none; outline:0 none;}
a:hover										{color:#000;  text-decoration:none;}
h1											{font-size:24px;}
h2											{font-size:22px;}
h3											{font-size:18px;}
h4											{font-size:16px;}
h5											{font-size:14px;}
h6											{font-size:12px;}
/*----------------------------------------------------------------------------------*/
html 										{ height:100%;}
body										{
	font-size: 14px;
	height: 100%;
}
.flt_lt										{float:left; display:inline;}
.flt_rt										{float:right; display:inline;}
/* ---------------------------------------------------------------------------------*/
	
	
	body {
		font-family: Arial;
		font-size: 12px;
	}
	
	.header {
		font-size: 13px;
		font-weight: bold;
		letter-spacing: -0.5px;
		padding-bottom: 3px;
	}
	
	.ordercontainer {
		display: block;
		width: 1030px;
		/*height: 670px;
		border: 1px solid red;
		-webkit-transform: rotate(90deg);
		-moz-transform: rotate(90deg);
		-o-transform: rotate(90deg);
		-ms-transform: rotate(90deg);
		transform: rotate(90deg);
		position: absolute;
		top: 100px;
		left: -95px;*/
		padding: 45px;
		margin: 0px auto;
	}
	
	.gridcontainer {
		width: 100%;
		border: 1px solid #000000;
		border-collapse: collapse;
	}
	
	.gridcontainer td {
		border: 1px solid #000000;
		vertical-align: text-top;
		position: relative;
		font-size: 10px;
	}
	
	.minilabel {
		position: absolute;
		top: 1px;
		left: 4px;
		font-size: 10px;
	}
	
	.label {
		display: block;
		position: relative;
		padding: 1px 0px;
		font-size: 12px;
		text-align: center;
	}
	
	.rotate {
		-webkit-transform: rotate(-90deg);
		-moz-transform: rotate(-90deg);
		-o-transform: rotate(-90deg);
		-ms-transform: rotate(-90deg);
		transform: rotate(-90deg);
		top: 40px;
		left: -10px;
	}
	
	.rotate2 {
		-webkit-transform: rotate(-90deg);
		-moz-transform: rotate(-90deg);
		-o-transform: rotate(-90deg);
		-ms-transform: rotate(-90deg);
		transform: rotate(-90deg);
		margin-top: 20px;
	}
	
	.paddingtop {
		padding-top: 15px;
	}
	
	.paddingtop2 {
		padding-top: 12px;
	}
	
	.right {
		text-align: right;
	}
	
	.height1 {
		right: 2px;
	}
	
	.vertalign td {
		 vertical-align: middle;
	}
	
	.pagebreak { page-break-before: always; }
	
	.printshow {
		display: none;
	}
	
	.celldata td {
		padding-left: 6px;
	}
	
	@media print{@page {
		size: landscape;
		}
		.ordercontainer {
			width: 940px;
			padding: 0px;
			margin: 0px;
		}
		.printshow {
			display: table-row;
		}
		}
	
</style>
</head>

<body>
<?php $count = $data->count();
$pages = ceil(($count - 6) / 7) + 1;
$page = 1;
?>
<div class="ordercontainer">	
	<?php displaybanner($model, $user, $page, $pages, true) ?>
			<table class="gridcontainer">
				
				<?=displayheader(false)?>
				
				<?php
				
				foreach($data as $index => $model2):
                $line = $index+1;
                ?>				
				<tr style="height: 25px;" class="vertalign celldata">
					<td><?=h($line)?></td>
					<td><?=h($model2->display("quantity"))?></td>
					<td><?=h($model2->display("series"))?></td>
					<td><?=h($model2->display("gage"))?></td>
					<td><?=h($model2->display("matl"))?></td>
					<td><?=h($model2->display("thk"))?></td>
					<td><?=h($model2->display("rabbet"))?></td>
					<td><?=h($model2->display("type"))?></td>
					<td><?=(!empty($model2['specialdepth']) ? h($model2->display('specialdepth')) : h($model2->display('depth')))?></td>
					<td><?=(!empty($model2['specialwidth']) ? h($model2->display('specialwidth')) : h($model2->display('width')))?></td>
					<td><?=(!empty($model2['specialheight']) ? h($model2->display('specialheight')) : h($model2->display('height')))?></td>
					<td><?=h($model2->display("strike"))?></td>
					<td><?=h($model2->display("loc"))?></td>
					<td><?=h($model2->display("second"))?></td>
					<td><?=h($model2->display("hand"))?></td>
					<td><?=h($model2->display("profile"))?></td>
					<td><?=h($model2->display("label"))?></td>
					<td><?=h($model2->display("assy"))?></td>
					<td><?=h($model2->display("anc"))?></td>
					<td><?=h($model2->display("hinge"))?></td>
					<td><?=h($model2->display("hingeqty"))?></td>
					<td><?=h($model2->display("hingeloc"))?></td>
					<td><?=h($model2->display("closer"))?></td>
					<td><?=h($model2->display("bolt"))?></td>
					<td><?=(!empty($model2['backbend']) && $model2['backbend'] != 187 ? " +".h($model2->display('backbend')) : '').($model2['cj'] ? " +CJ" : '').($model2['dtch'] ? " +DTCH" : '').($model2['gb'] ? " +GB" : '').(!empty($model2['openings']) ? " +".h($model2->display('openings'))." openings" : '')?></td>
					<td>&nbsp;</td>
				</tr>
				<tr style="height: 36px;" class="vertalign"><td colspan="27">TAG / Mark #'s: <?=(!empty($model2['add']) ? h($model2->display('add')) : '')?></td></tr>
				<? if ($line == 6 || ($line > 7 && (($line-6) % 7 == 0) && $line < $count)) {
					$page++;
					?></table>
		<div class="pagebreak"></div>
		<?php displaybanner($model, $user, $page, $pages) ?>
		<table class="gridcontainer">
			<?=displayheader(false)?>
			<? } ?>
				
				<?php endforeach; ?>
				
			</table>
</div>
</body>
</html>