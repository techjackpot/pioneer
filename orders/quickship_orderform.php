<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged())
redirect_to( '/' );

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

function displayheader($printshow = false) {
	return '<tr>
			<td colspan="2">Line</td>
			<td colspan="9">General Frame Information</td>
			<td colspan="3">Lock Information</td>
			<td>Hand</td>
			<td colspan="4">Label / Assembly</td>
			<td colspan="3">Hinge</td>
			<td colspan="2">Other</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
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
		font-size: 12px;
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
<div class="ordercontainer">
	<div class="header">Pioneer Industries &nbsp;Tel: (201 933-1900 &#8226; Fax: (201) 933-580&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PURCHASE ORDER&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;QUICK SHIP</div>
	<table class="gridcontainer">
		<tr><td width="240" class="paddingtop"><div class="minilabel">CUSTOMER ORDER NUMBER</div>QS<?=h($model->display("id"));?></td><td width="240" class="paddingtop"><div class="minilabel">DATE</div><?=h($model->display("created_at"));?></td><td width="87" class="paddingtop"><div class="minilabel">CUSTOMER CODE</div>QS<?=h($model->display("user_id"));?></td><td rowspan="2" width="134" class="paddingtop"><div class="minilabel">JOB NUMBER</div>&nbsp;</td></tr>
		<tr><td rowspan="3" width="240"><div class="minilabel rotate">SOLD TO</div><div style="padding-left: 30px"><?=$model->billto()?></div></td><td rowspan="3" width="240"><div class="minilabel rotate">SHIP TO</div><div style="padding-left: 30px"><?=$model->shipto()?></div></td><td class="paddingtop" width="87"><div class="minilabel">SHIPPING CODE</div><div style="height: 14px; overflow: hidden;"><?=h($model->display("shipping_code"));?></div></td></tr>
		<tr><td class="paddingtop"><div class="minilabel">DATE ENTERED</div>&nbsp;</td><td class="paddingtop"><div class="minilabel">PAGE NO. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; OF</div>&nbsp;</td></tr>
		<tr><td class="paddingtop"><div class="minilabel">DISCOUNT</div>&nbsp;</td><td class="paddingtop">&nbsp;</td></tr>
		<tr><td colspan="4" style="padding: 0px;">
			<table class="gridcontainer">
				
				<?=displayheader(false)?>
				
				<?php foreach($data as $index => $model2):
                $line = $index+1;
                ?>				
				<tr style="height: 25px;" class="vertalign">
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
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr style="height: 36px;" class="vertalign"><td colspan="27">TAG / Mark #'s:</td></tr>
				<? if ($line == 4 || ($line > 9 && (($line-4) % 6 == 0))) { ?></table>
		<div class="pagebreak"></div>
		<table class="gridcontainer">
			<?=displayheader(false)?>
			<? } ?>
				
				<?php endforeach; ?>
				
			</table>
		</td></tr>
	</table>
</div>