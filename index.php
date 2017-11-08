<?php
define('INDEX', true);

require_once 'common.php';

$path = decode_path();

/*
if (DEV == false)
{
	if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on' && ($path[0] == 'admin' || $path[0] == 'secureajax' || ($path[0] == 'events' && $path[2] == 'registration')))
	{
		header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
	}
	else if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' && ($path[0] != 'admin' && $path[0] != 'secureajax' && ($path[0] != 'events' && $path[2] != 'registration')))
	{
		header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
	}
}
*/


if ($path[0] != 'resize')
require_once($path[0] === 'admin' ? 'template_admin.php' : (QS_URL == HOSTNAME ? 'quickship_template.php' :'template.php'));

ob_start();

if (QS_URL == HOSTNAME)
{
	switch($path[0]):
	case 'orderconfirmation':
	case 'finalizeorder':
	case 'deleteframe':
	case 'revieworder':
		require 'front/quickship_revieworder.php';
	break;
	case 'thankyou':
		require 'front/quickship_thankyou.php';
	break;
	case 'guidelines':
		require 'front/quickship_guildlines.php';
	break;
	case 'copyorder':
	case 'orderhistory':
		require 'front/quickship_orderhistory.php';
	break;
	case 'orderform':
		require 'front/quickship_orderform.php';
	break;
	case 'downloadorders':
		require 'front/quickship_downloadorders.php';
	break;
	case '':
		require 'front/quickship_home.php';
	break;
	endswitch;
}

switch($path[0]):
case 'admin':
	require 'admin.php';
break;
case 'sec_ajax':
	require 'front/ajax.php';
break;
case 'logout':
	require 'logout.php';
break;
case 'editframe':
case 'addframe':
case 'copyline':
	require 'front/quickship_addframe.php';
break;
case 'distributers':
case 'projects':
case 'about-pioneer':
case 'contact-us':
	require 'front/'.$path[0].'.php';
break;
case '':
	require 'front/home.php';
break;
default:
	if ($path[0] === 'resize') require 'lib/image.php';
	else if ($path[0] === 'SLIR') require 'lib/SLIR/install';
	else if ($path[0] === 'checkemail') require 'check_email.php';
	else if ($path[0] === 'loginajax') require 'loginajax.php';
	else if ($path[0] === 'secureajax') require 'secureajax.php';
	else if ($path[0] === 'login') require 'loginfront.php';
	else if ($path[0] === 'forgotpassword') require 'forgotpassword.php';
	else {
		for ($p=0; $p<=10; $p++)
		{
			if (!empty($path[$p]))
			$newpath = $path[$p];
		}
		$page = sqla("SELECT `id` FROM pages WHERE uri = %s", $newpath);
		if ($page)
			require 'pages.php';
		else
			error_404();
	}
break;
endswitch;

if (is_json()) {
	header("Content-type: application/json");
	ob_end_flush();
}
else if (is_modal() || is_paginator() || is_reload() || $no_template) {
	ob_end_flush();
}
else {
	$contents = ob_get_contents();
	ob_end_clean();

	create_header($pageTitle);
	echo $contents;
	
	if (!($path[0] == "programs" && $path[1] == "business"))
	{
		create_footer();
	}
}