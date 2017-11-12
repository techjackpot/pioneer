<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

if (!is_logged() && $path[1] !== 'login') { redirect_to('/admin/login?return='.urlencode( '/'.implode('/', $path) )); }
else if (is_logged() && $path[0] == "admin" && user_type() <= User::CUSTOMER) { redirect_to('/'); }

switch($path[1]):
case 'login':
	require 'login.php';
break;
case 'logout':
	require 'logout.php';
break;
case 'settings':
case 'search':
case 'changelog':
case 'typeahead':
case 'selectajax':
	require "admin/{$path[1]}.php";
break;
case 'vendors':
case 'projects':
case 'frameproducts':
case 'files':
case 'pages':
	switch($path[2]):
	case 'add':
	case 'edit':
	case 'delete':
		require $path[1].'/'.substr($path[1], 0, -1).'_form.php';
	break;
	case '':
		require $path[1].'/'.substr($path[1], 0, -1).'_list.php';
	break;
	default:
		error_404();
	break;
	endswitch;
break;
case 'orders':
	switch($path[2]):
	case 'add':
	case 'edit':
	case 'delete':
		require $path[1].'/'.substr($path[1], 0, -1).'_form.php';
	break;
    case 'markpending':
    case 'markprocessed':
	case '':
		require $path[1].'/'.substr($path[1], 0, -1).'_list.php';
	break;
	default:
		error_404();
	break;
	endswitch;
break;
case 'frameoptions':
	switch($path[2]):
	case 'add':
	case 'edit':
	case 'delete':
		require $path[1].'/'.substr($path[1], 0, -1).'_form.php';
	break;
    case 'values':
		require $path[1].'/'.substr($path[1], 0, -1).'_options.php';
	break;
    case 'deletevalue':
    case 'editvalue':
    case 'addvalue':
		require $path[1].'/'.substr($path[1], 0, -1).'_editvalue.php';
	break;
	case '':
		require $path[1].'/'.substr($path[1], 0, -1).'_list.php';
	break;
	default:
		error_404();
	break;
	endswitch;
break;
case 'people':
	switch($path[2]):
	case 'add':
	case 'edit':
    case 'access':
	case 'reset_password':
	case 'delete':
		require 'users/people_form.php';
	break;
	case 'history':
		require 'users/people_history.php';
	break;
	case 'permissions':
		require 'users/permissions_admin.php';
	break;
	case '':
		require 'users/people_list.php';
	break;
	default:
		error_404();
	break;
	endswitch;
break;
case 'organizations':
	switch($path[2]):
	case 'add':
	case 'edit':
	case 'delete':
		require $path[1].'/'.substr($path[1], 0, -1).'_form.php';
	break;
	case 'assign':
		require $path[1].'/'.substr($path[1], 0, -1).'_'.$path[2].'.php';
	break;
	case '':
		require $path[1].'/'.substr($path[1], 0, -1).'_list.php';
	break;
	default:
		error_404();
	break;
	endswitch;
break;
case '':
	require 'admin/dashboard.php';
break;
default:
	error_404();
break;
endswitch;