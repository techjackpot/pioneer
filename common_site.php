<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

foreach(array('user', 'organization', 'page', 'notification', 'frameproduct', 'frameoption', 'order', 'project', 'vendor', 'file') as $m) { require dirname(__FILE__)."/{$m}s/{$m}_model.php"; }

$__settings = array(
	'timezone' => array('type' => 'text', 'default' => 'America/New_York', 'display' => 'General'),
	'authnet_loginid' => array('type' => 'text', 'label' => 'Auth.net Login ID', 'default' => '', 'display' => 'Authorize.net'),
	'authnet_transkey' => array('type' => 'text', 'label' => 'Auth.net Transaction Key', 'default' => '', 'display' => 'Authorize.net'),
	'authnet_sandbox' => array('type' => 'checkbox', 'label' => 'Auth.net Sandbox', 'default' => '1', 'display' => 'Authorize.net'),
	'exchange_server' => array('type' => 'text', 'default' => '', 'display' => 'Exchange'),
	'exchange_username' => array('type' => 'text', 'default' => '', 'display' => 'Exchange'),
	'exchange_password' => array('type' => 'text', 'default' => '', 'display' => 'Exchange'),
	'exchange_version' => array('type' => 'text', 'default' => '', 'display' => 'Exchange'),
);

function admin_url($model = null, $id = null, $id2 = null, $id3 = null) {
	if ($model instanceof Model) { $model = $model::$table; }
	
	$url = BASE_PATH.'admin/';
	switch($model) {
		case 'PermissionGroup':
		case 'users_groups':
			$url .= 'people/permissions';
		break;
		case 'User':
		case 'users':
			$url .= 'people';
		break;
		
		case 'vendors':
		case 'projects':
		case 'orders':
		case 'frameoptions':
	    case 'frameproducts':
		case 'pages':
		case 'files':
		case 'organizations':
			$url .= $model;
		break;
		case 'Template':
			$url .= strtolower( $model ).'s';
		break;
		case null: break;
		default:
			return false;
		break;
	}
	if ($id2 === null) {
		if ($id === true) { $url .= '/add'; }
		elseif ($id < 0) { $url .= '/delete/'.-$id; }
		elseif ($id > 0) { $url .= '/edit/'.$id; }
		elseif ($id !== null) { $url .= '/'.$id; }
	}
	
	else {
		$url .= '/'.urlencode($id).'/'.urlencode($id2);
	}
	
	if ($id3 !== null) {
		$url .= '#'.$id3;
	}
	
	return $url;
}

function model_from_table($table) {
	switch($table) {
		case 'projects': return 'Project';
		case 'users_groups': return 'PermissionGroup';
		case 'users': return 'User';
		case 'frameoptions': return 'FrameOption';
		case 'frameproducts': return 'FrameProducts';
		case 'frameoptionvalues': return 'FrameOptionValue';
	}
}

function save_to_temp_file($contents) {
	$tmpfname = tempnam(sys_get_temp_dir(), SITEID.'ics');
	file_put_contents($tmpfname, $contents);
	return $tmpfname;
}

class Country extends Model {
	public static $table = "locations";
}