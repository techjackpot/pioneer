<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/index.php'; exit; }

require dirname(__FILE__).'/config.php';
date_default_timezone_set('GMT');

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 100);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 100);

session_start();
$__is_logged = null;
$__user_type = 0;
function user_id() {
	global $__is_logged, $__user_type;
	if (!isset($__is_logged)) {
		 $__is_logged = false;
		if (isset($_SESSION['uid'])) {
			if (list($id, $type) = sqll("SELECT id, type_id FROM users WHERE id = %s", intval($_SESSION['uid']))) {
				$__is_logged = intval($id);
				$__user_type = intval($type);
			}
		}
	}
	return $__is_logged;
}
function user_type() {
	global $__is_logged, $__user_type;
	if (user_id()) return $__user_type;
	else return 0;
}
function is_logged() {
	return !!user_id();
}

function is_home() {
	if ($_SERVER['REQUEST_URI'] === "/") return true; else return false;
}

function icon($icon, $name = null) {
	return '<i class="icon-'.$icon.'"></i>'.(is_null($name)?'':' '.h($name));
}
function iconw($icon, $name = null) {
	return '<i class="icon-'.$icon.' icon-white"></i>'.(is_null($name)?'':' '.h($name));
}

function truncate($text, $length = 100) {
	if (strlen($text) > $length) {
		return preg_replace('/^([\s\S]{0,'.($length-2).'})([\s.,;:][\s\S]*)$/', '\1...', $text);
	}
	else return $text;
}

function redirect_to($url = null) {
	roll_over_status_messages();
	if ($url === null) { header("Location: ".SITE_URL.$_SERVER['REQUEST_URI']); }
	else { header("Location: ".SITE_URL.$url); }
	die();
}

function seourl($string) {
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}

if (isset($_SESSION['error'])) {
	$error_message = $_SESSION['error'];
	unset($_SESSION['error']);
}
else { $error_message = array(); }

if (isset($_SESSION['success'])) {
	$success_message = $_SESSION['success'];
	unset($_SESSION['success']);
}
else { $success_message = array(); }

if (isset($_SESSION['information'])) {
	$information_message = $_SESSION['information'];
	unset($_SESSION['information']);
}
else { $information_message = array(); }

function roll_over_status_messages() {
 	global $error_message, $success_message, $information_message;
 	$_SESSION['error'] = $error_message;
 	$_SESSION['success'] = $success_message;
 	$_SESSION['information'] = $information_message;
}

function success_message($msg) {
	if (func_num_args() > 1) { $msg = vsprintf($msg, array_slice(func_get_args(), 1)); }
	$GLOBALS['success_message'][] = $msg;
}
function error_message($msg) {
	if (func_num_args() > 1) { $msg = vsprintf($msg, array_slice(func_get_args(), 1)); }
	$GLOBALS['error_message'][] = $msg;
}
function information_message($msg) {
	if (func_num_args() > 1) { $msg = vsprintf($msg, array_slice(func_get_args(), 1)); }
	$GLOBALS['information_message'][] = $msg;
}

$__messages_show = false;
function status_messages() {
 	global $error_message, $success_message, $information_message, $__messages_shown;
 	$ret = '';
	foreach($error_message as $message) { $ret .= status_message($message, 'error'); }
	foreach($success_message as $message) { $ret .= status_message($message, 'success'); }
	foreach($information_message as $message) { $ret .= status_message($message, 'info'); }
	$__messages_shown = true;
	return $ret;
}
function status_message($message, $class = null) {
	return '<div class="alert'.(!is_null($class)?" alert-$class":'').'"><a class="close" data-dismiss="alert">×</a>'.$message.'</div>';
}

function decode_path() {
	preg_match('/^'.preg_quote(BASE_PATH, '/').'(.*?)(\?.*)?$/', $_SERVER['REQUEST_URI'], $matches);
	return array_map("urldecode", explode('/', strtolower($matches[1])));
}

function h($value) {
	//if (is_array($value))
	//$value = implode(",", $value);
	
	return htmlspecialchars($value);
}

function unslash($value) {
	if (!get_magic_quotes_gpc()) return $value;
	else if (is_null($value)) { return null; }
	else if (is_array($value)) { return array_map('unslash', $value); }
	else { return stripslashes($value); }
}

function cast_ids_to_int(&$array) {
	foreach ($array as $key => $value) {
		if (key_is_id($key)) {
			$array[$key] = to_int($value);
			if (is_array($array[$key])) { sort($array[$key], SORT_NUMERIC); }
		}
	}
}

function key_is_id($value) {
	return !!preg_match('/(^|_)ids?$/i', $value);
}

function to_int($value) {
	if ($value === "" || is_null($value)) { return null; }
	else if (is_array($value)) { return array_map('to_int', $value); }
	else { return intval($value); }
}

function blank($value) {
	if (is_array($value)) {
		foreach($value as $item) {
			if (!blank($item)) return false;
		}
		return true;
	}
	return $value === null || $value === '';
}

function presence($value) {
	if (blank($value)) return null;
	else return $value;
}

function label_from_name($name) {
	return preg_replace('/ id(s?)$/i', '\1', ucwords(str_replace('_',' ', $name)));
}

function squash($value) {
	return preg_replace(array('/\s\s+/', '/^\s+|\s+$/'), array(' ', ''), $value);
}

function character_limiter($str, $n = 500, $end_char = '&#8230;')
{
    if (strlen($str) < $n)
    {
        return $str;
    }

    $str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

    if (strlen($str) <= $n)
    {
        return $str;
    }

    $out = "";
    foreach (explode(' ', trim($str)) as $val)
    {
        $out .= $val.' ';

        if (strlen($out) >= $n)
        {
            $out = trim($out);
            return (strlen($out) == strlen($str)) ? $out : $out.$end_char;
        }
    }
 }

function format_dt($value, $format = 'M j, Y g:ia', $reverse = false) {
	if ($value === null) return '';
	if (is_int($value)) {
		$datetime = new DateTime();
		$datetime->setTimestamp($value);
	}
	else {
		$datetime = new DateTime("$value GMT");
		$datetime->setTimezone(timezone());
	}
	
	if ($_SESSION['timezoneoffset'])
	{
	if ($reverse)
	{
	if (substr($_SESSION['timezoneoffset'], 0, 1) == "+")
	$newoffset = preg_replace("/^+/", "-", $_SESSION['timezoneoffset']);
	else if (substr($_SESSION['timezoneoffset'], 0, 1) == "-")
	$newoffset = preg_replace("/^-/", "+", $_SESSION['timezoneoffset']);
	else
	$newoffset = $_SESSION['timezoneoffset'];
	
	return date($format, $datetime->format('U') + $newoffset);
	}
	else
	return date($format, $datetime->format('U') + $_SESSION['timezoneoffset']);
	}
	else
	return $datetime->format($format);
}

function parse_dt($value) {
	static $datetime;
	if ($value === null) { return null; }
	$datetime = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s',strtotime($value)), timezone());
	return $datetime->getTimestamp();
}

function timezone() {
	static $tz;
	if (!$tz) {
		$tz = new DateTimeZone(settings('timezone'));
	}
	return $tz;
}

function sql_time($time = null) {
	if (is_null($time)) { $time = time(); }
	return gmdate('Y-m-d H:i:s', $time);
}

function active_if($boolean) {
	return $boolean ? ' class="active" ' : '';
}

function debug() {
	foreach(func_get_args() as $arg) highlight_string("<?php\n".var_export($arg, true));
	// echo "<pre>";
	// foreach(func_get_args() as $arg) var_dump($arg);
	// echo "</pre>";
}
	
function is_modal() {
	return $_SERVER['HTTP_DZ_OUTPUT'] === 'modal';
}
	
function is_paginator() {
	return $_SERVER['HTTP_DZ_OUTPUT'] === 'paginator';
}
	
function is_reload() {
	return $_SERVER['HTTP_DZ_OUTPUT'] === 'reload';
}
	
function is_json() {
	return $_SERVER['HTTP_DZ_OUTPUT'] === 'json' || array_key_exists('json', $_GET);
}

function json_response($data) {
 	global $error_message, $success_message, $information_message, $__messages_shown;
 	$ret = '';
	foreach($error_message as $message) { $data['msgs']['error'][] = $message; }
	foreach($success_message as $message) { $data['msgs']['success'][] = $message; }
	foreach($information_message as $message) { $data['msgs']['info'][] = $message; }
	header("Content-type: application/json");
	echo json_encode($data);
	exit;	
}

function make_permalink($str, $table, $id = null, $column = 'permalink') {
	if (!$id) { $id = 0; }
	$base = slugify($str);
	$slug = $base;
	for($i = 1; sqlr("SELECT 1 FROM `$table` WHERE `$column` = %s AND id <> %s", $slug, $id); $i++) {
		$slug = $base.'-'.$i;
	}
	return $slug;
}

function slugify($str) {
	$str = strtolower($str);
	$str = preg_replace('/\'+/','', $str);
	$str = preg_replace('/[^\w]+/', '-', $str);
	$str = preg_replace('/^_+/', '', $str);
	$str = preg_replace('/_+$/', '', $str);
	$str = preg_replace('/_+/', '-', $str);
	return $str;
}

function settings($key) {
	global $__settings;
	// if (!array_key_exists($key, $__settings)) { return; }
	static $cached;
	if ($cached === null) {
		$cached = array();
		$sql = sql("SELECT `key`, `value` FROM `settings` WHERE `auto_load` = 1");
		while(list($k, $v) = $sql->fetch_row()) { $cached[$k] = $v; }
	}
	if (!array_key_exists($key, $cached)) {
		$result = sqlr("SELECT `value` FROM `settings` WHERE `key` = %s", $key);
		if ($result === null) { $cached[$key] = $__settings[$key]['default']; }
		else { $cached[$key] = $result; }
	}
	return $cached[$key];
}

function settings_update($key, $value) {
	return sql("INSERT INTO `settings` (`key`, `value`, `updated_at`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`)", $key, $value, gmdate('Y-m-d H:i:s'));
}

function url() {
	foreach(func_get_args() as $arg) {
		if (is_array($arg)) {
			foreach($arg as $a) $ret .= '/'.urlencode($a);
		}
		else $ret .= '/'.urlencode($arg);
	}
	return $ret;
}

function error_403() {
	define('DENIED', true);
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden",  true, 403);

	while (ob_get_level()) { ob_end_clean(); }
	
	$msg = user_id() ? 'You have insufficient privileges to view this page.' : 'You must be logged in with an account to continue.';
	
	if (is_json()) {
		header("Content-type: application/json");
		echo json_encode(array('success' => false, 'msgs' => array('error' => $msg)));
	}
	else if (is_modal()) {
?>
<div class='modal-dialog'>
	<div class='modal-content'>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h4 class="modal-title">Access Denied</h4>
		</div>
		<div class="modal-body">
			<p><?= $msg ?></p>
		</div>
		<div class="modal-footer"> 
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
<?php
	}
	else {
		$GLOBALS['pageTitle'] = 'Access Denied';
		create_header();
?>
		<h2 class="page-title">Access Denied</h2>
		<p><?= $msg ?></p>
<?php
		create_footer();
	}
	exit;
}

function error_404() {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);

	while (ob_get_level()) { ob_end_clean(); }
	
	if (is_json()) {
		header("Content-type: application/json");
		echo json_encode(array('success' => false, 'msgs' => array('error' => 'Page Not Found')));
	}
	else if (is_modal()) {
?>
<div class='modal-dialog'>
	<div class='modal-content'>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
			<h4 class="modal-title">Page Not Found</h4>
		</div>
		<div class="modal-body">
			<p>The page you were looking for doesn't exist.</p>
			<p>You may have mistyped the address or the page may have moved.</p>
		</div>
		<div class="modal-footer"> 
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
<?php
	}
	else {
		$GLOBALS['pageTitle'] = 'Page Not Found';
		create_header();
?>

		<h2 class="page-title">The page you were looking for doesn't exist.</h2>
		<h4>You may have mistyped the address or the page may have moved.</h4>
<?php
		create_footer();
	}
	exit;
}

function &get_countries() {
	static $countries;
	if ($countries === null) {
		$countries = array();
		$q = sql("SELECT name FROM locations WHERE target_type = 'Country' ORDER BY name");
		while(list($name) = $q->fetch_row()) { $countries[$name] = $name; }
	}
	return $countries;
	
}

function &get_regions() {
	static $regions;
	if ($regions === null) {
		$regions = array();
		$q = sql("SELECT loc.name FROM locations as loc WHERE loc.target_type = 'state' OR loc.target_type = 'province' OR loc.target_type = 'Region' ORDER BY loc.name");
		while(list($name) = $q->fetch_row()) { $regions[$name] = $name; }
	}
	return $regions;
}

function &get_cities() {
	static $cities;
	if ($cities === null) {
		$cities = array();
		$q = sql("SELECT name FROM locations WHERE target_type = 'City' ORDER BY name");
		while(list($name) = $q->fetch_row()) { $cities[$name] = $name; }
	}
	return $cities;
	
}

function &get_times() {
	$starttime = '00:00:00';
	$time = new DateTime($starttime);
	$interval = new DateInterval('PT15M');
	$temptime = $time->format('H:i:s');
	
	do {
	   $output[] = $temptime;
	   $time->add($interval);
	   $temptime = $time->format('H:i:s');
	} while ($temptime !== $starttime);
	
	return $output;
}

function get_full_path($id)
{
	$fullpath = array();
	$page = sqla("SELECT `uri`, `parent_id` FROM pages WHERE id = ".sanitize($id));
	if ($page)
	$fullpath[] = "/".$page['uri'];
	if (!empty($page['parent_id']))
	{
	$fullpath[] = get_full_path($page['parent_id']);
	}
	$ret = implode("", array_reverse($fullpath));
	return $ret;
}

function __autoloader($className) {
	$className = preg_replace("'[^A-z0-9_\\\\]'isu", '', $className);
	$path = PROJ_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.str_replace(array('\\', '_'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $className).'.php';
	if (is_readable($path)) return (bool) include($path);
	else return false;
}

function cms_content($id)
{
	$page = sqla("SELECT `content` FROM pages WHERE id = ".sanitize($id));
	if ($page)
	{
	$ret = preg_replace_callback("'\{(\w+)\}'i", function ($m) { return parsed_content(strtolower($m[1])); }, $page['content']);
	return $ret;
	}
	else
	return false;
}

function parsed_content($var) {
ob_start();
switch($var) {
case "executive_profiles":

break;
case "board_profiles":

break;
case "business_buttons":

break;
case "government_buttons":

break;
case "resources_gallery":

break;
default:
break;
}
$ret = ob_get_clean();
return $ret;
}

function is_image($mediapath)
{
    if(@!is_array(getimagesize($mediapath))){
    	return true;
	} 
	else {
		return false;
	}
}

function check_password($pwd) {
	
	if( strlen($pwd) < 6  || strlen($pwd) > 20 ) {
		$error[] = "Must be between 6-20 characters long.";
	}

	if( !preg_match("#[0-9]+#", $pwd) ) {
		$error[] = "Include at least one number."; 
	}


	if( !preg_match("#[a-z]+#", $pwd) ) {
		$error[] = "Include at least one letter.";
	}


	if( !preg_match("#[A-Z]+#", $pwd) ) {
		$error[] = "Include at least one capitol letter.";
	}

	if($error){
		return $error;
	} else {
		return true;
	}
}

function convertcountrycode($cuntcode) {
$allCountries = [ 'af' => "93" , 'al' => "355" , 'dz' => "213" , 'as' => "1684" , 'ad' => "376" , 'ao' => "244" , 'ai' => "1264" , 'ag' => "1268" , 'ar' => "54" , 'am' => "374" , 'aw' => "297" , 'au' => "61" , 'at' => "43" , 'az' => "994" , 'bs' => "1242" , 'bh' => "973" , 'bd' => "880" , 'bb' => "1246" , 'by' => "375" , 'be' => "32" , 'bz' => "501" , 'bj' => "229" , 'bm' => "1441" , 'bt' => "975" , 'bo' => "591" , 'ba' => "387" , 'bw' => "267" , 'br' => "55" , 'io' => "246" , 'vg' => "1284" , 'bn' => "673" , 'bg' => "359" , 'bf' => "226" , 'bi' => "257" , 'kh' => "855" , 'cm' => "237" , 'ca' => "1", 'bq' => "599" , 'ky' => "1345" , 'cf' => "236" , 'td' => "235" , 'cl' => "56" , 'cn' => "86" , 'co' => "57" , 'km' => "269" , 'cd' => "243" , 'cg' => "242" , 'ck' => "682" , 'cr' => "506" , 'ci' => "225" , 'hr' => "385" , 'cu' => "53" , 'cw' => "599" , 'cy' => "357" , 'cz' => "420" , 'dk' => "45" , 'dj' => "253" , 'dm' => "1767" , 'do' => "1" , 'ec' => "593" , 'eg' => "20" , 'sv' => "503" , 'gq' => "240" , 'er' => "291" , 'ee' => "372" , 'et' => "251" , 'fk' => "500" , 'fo' => "298" , 'fj' => "679" , 'fi' => "358" , 'fr' => "33" , 'gf' => "594" , 'pf' => "689" , 'ga' => "241" , 'gm' => "220" , 'ge' => "995" , 'de' => "49" , 'gh' => "233" , 'gi' => "350" , 'gr' => "30" , 'gl' => "299" , 'gd' => "1473" , 'gp' => "590" , 'gu' => "1671" , 'gt' => "502" , 'gn' => "224" , 'gw' => "245" , 'gy' => "592" , 'ht' => "509" , 'hn' => "504" , 'hk' => "852" , 'hu' => "36" , 'is' => "354" , 'in' => "91" , 'id' => "62" , 'ir' => "98" , 'iq' => "964" , 'ie' => "353" , 'il' => "972" , 'it' => "39" , 'jm' => "1876" , 'jp' => "81" , 'jo' => "962" , 'kz' => "7" , 'ke' => "254" , 'ki' => "686" , 'kw' => "965" , 'kg' => "996" , 'la' => "856" , 'lv' => "371" , 'lb' => "961" , 'ls' => "266" , 'lr' => "231" , 'ly' => "218" , 'li' => "423" , 'lt' => "370" , 'lu' => "352" , 'mo' => "853" , 'mk' => "389" , 'mg' => "261" , 'mw' => "265" , 'my' => "60" , 'mv' => "960" , 'ml' => "223" , 'mt' => "356" , 'mh' => "692" , 'mq' => "596" , 'mr' => "222" , 'mu' => "230" , 'mx' => "52" , 'fm' => "691" , 'md' => "373" , 'mc' => "377" , 'mn' => "976" , 'me' => "382" , 'ms' => "1664" , 'ma' => "212" , 'mz' => "258" , 'mm' => "95" , 'na' => "264" , 'nr' => "674" , 'np' => "977" , 'nl' => "31" , 'nc' => "687" , 'nz' => "64" , 'ni' => "505" , 'ne' => "227" , 'ng' => "234" , 'nu' => "683" , 'nf' => "672" , 'kp' => "850" , 'mp' => "1670" , 'no' => "47" , 'om' => "968" , 'pk' => "92" , 'pw' => "680" , 'ps' => "970" , 'pa' => "507" , 'pg' => "675" , 'py' => "595" , 'pe' => "51" , 'ph' => "63" , 'pl' => "48" , 'pt' => "351" , 'pr' => "1" , 'qa' => "974" , 're' => "262" , 'ro' => "40" , 'ru' => "7" , 'rw' => "250" , 'bl' => "590" , 'sh' => "290" , 'kn' => "1869" , 'lc' => "1758" , 'mf' => "590" , 'pm' => "508" , 'vc' => "1784" , 'ws' => "685" , 'sm' => "378" , 'st' => "239" , 'sa' => "966" , 'sn' => "221" , 'rs' => "381" , 'sc' => "248" , 'sl' => "232" , 'sg' => "65" , 'sx' => "1721" , 'sk' => "421" , 'si' => "386" , 'sb' => "677" , 'so' => "252" , 'za' => "27" , 'kr' => "82" , 'ss' => "211" , 'es' => "34" , 'lk' => "94" , 'sd' => "249" , 'sr' => "597" , 'sz' => "268" , 'se' => "46" , 'ch' => "41" , 'sy' => "963" , 'tw' => "886" , 'tj' => "992" , 'tz' => "255" , 'th' => "66" , 'tl' => "670" , 'tg' => "228" , 'tk' => "690" , 'to' => "676" , 'tt' => "1868" , 'tn' => "216" , 'tr' => "90" , 'tm' => "993" , 'tc' => "1649" , 'tv' => "688" , 'vi' => "1340" , 'ug' => "256" , 'ua' => "380" , 'ae' => "971" , 'gb' => "44" , 'us' => "1" , 'uy' => "598" , 'uz' => "998" , 'vu' => "678" , 'va' => "39" , 've' => "58" , 'vn' => "84" , 'wf' => "681" , 'ye' => "967" , 'zm' => "260" , 'zw' => "263" ];

return $allCountries[$cuntcode];

}

function quickship_navigation($path) {
	?><div class="qsnav <?=(empty($_SESSION['OID']) ? 'nobrdr' : '')?>">
			<ul>
			<li <?=($path[0] == 'guidelines' ? 'class="selected"' : '')?>><a href="/guidelines">Ordering Guidelines</a></li>
			<? if (!empty($_SESSION['OID'])) { ?>
			<li class="<?=(empty($_SESSION['OID']) ? 'inactive' : '')?> <?=($path[0] == 'revieworder' || $path[0] == 'finalizeorder' ? 'selected' : '')?>"><a href="/revieworder" >Review Order</a></li>
			<li class="<?=(empty($_SESSION['OID']) ? 'inactive' : '')?> <?=($path[0] == 'addframe' ? 'selected' : '')?>"><a href="/addframe" >Add New Line</a></li>
			<?php }
			else
			{ ?><li>&nbsp;</li><li>&nbsp;</li><?php } ?></ul>
		</div>
	<?php
}

function quickship_order_summary($model) {
	
	$frameprods = FrameProduct::find();
	$framestart = FrameProduct::first("`id` IS NOT NULL", ['sort' => '`id` ASC']);
	
	if ($model)
	{
		$turnaround_days = $model->display("turnaround_days");
		$qtylimit = $model->display("quantity_limit");
		$qtycart = $model->cart_quantity();
		$percentfull = $qtycart / $qtylimit;
	}
	else
	{
		$turnaround_days = h($framestart->display('turnaround_days'));
		$qtycart = 0;
		$qtylimit = h($framestart->display('quantity_limit'));
		$percentfull = 0.00;
	}
	
			?><div class="qsnav">
                <ul><li class="nolink">Order Summary</li></ul>
            </div>
            <form method="post" class="mainform">
            <div class="qstimelineselect">
                <p>QUICKSHIP TIMELINE:</p>
                <?php if (!$model) { ?>
                <div class="control-group"> 
                    <div class="controls">
                        <select name="frameproduct_id" id="frameproduct_id" class="input-medium">
							<? foreach ($frameprods as $index=>$data) : ?>
                            <option value="<?=$data['id']?>" data-days="<?=h($data->display('turnaround_days'))?>" data-limit="<?=h($data->display('quantity_limit'))?>"><?=h($data->display('turnaround_days'))?> Day</option>
							<? endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php } else { ?>
                <span class="selectedtl"><?=$model->display("product_name")?></span>
                <?php } ?>
             </div>
            <div class="limits">
                <div class="limittext">
                    <h6><span id="days"><?=$turnaround_days?></span> DAY</h6>
                    <h5><span class="darkblue" id="qty"><?=$qtycart?></span>/<span id="limit"><?=$qtylimit?></span></h5>
                </div>
                <div class="circle"></div>
            </div>
            <script>
                $('.circle').circleProgress({
                    value: <?=$percentfull?>,
                    size: 128,
                    fill: { image: "/images/color-gradient-6-1440x9003.png" }
                });
            </script>
            <div class="qsproceedactions">
            <?php if (empty($_SESSION['OID'])) { ?>
            <button name="neworder" value="submit" class="btn btn-blue" type="submit">Proceed With Order</button>
            <?php } else { ?>
            <a href="/finalizeorder" class="btn btn-blue submitbtn" onclick="showLoad(); return confirm('Are you sure? Once an order is submitted, it CANNOT be changed.');">Finalize Order</a>
            <?php } ?>
			<?php if (!empty($_SESSION['OID'])) { ?>
            <p>&nbsp;</p>
            <a href="/guidelines?cancel=1" class="btn btn-blue cancel" id="cancelorder" onclick="return confirm('Are you sure you would like to cancel this order?');">Cancel Order</a>
			<?php } ?>
            </div>
            </form><?php
}

function important_notice($path = null) {
	?>
	<div class="importantnotice">
		<h1 <?=($path[0] == 'guidelines' ? 'class= "addblink"' : '')?>>IMPORTANT:</h1>
		<ul>
			<li <?=($path[0] == 'guidelines' ? 'class= "addblink"' : '')?>>Orders MUST be placed before 12:00 EST. Orders after Noon will be considered received the following day.</li>
			<li <?=($path[0] == 'guidelines' ? 'class= "addblink"' : '')?>>Production Day One of the 5/10 days is the day after the order is received.</li>
			<li <?=($path[0] == 'guidelines' ? 'class= "addblink"' : '')?>>Once an order is entered, it CANNOT be changed.</li>
		</ul>
		<img src="/images/pioneerman.png" />
	</div>
	<?php
}

spl_autoload_register('__autoloader');

require dirname(__FILE__).'/helpers/mysql.php';
require dirname(__FILE__).'/helpers/csv.php';
require dirname(__FILE__).'/helpers/ChangeLog.php';
require dirname(__FILE__).'/helpers/FormHelper.php';
require dirname(__FILE__).'/helpers/TableHelper.php';
require dirname(__FILE__).'/helpers/model.php';
require dirname(__FILE__).'/helpers/Mail.php';
require dirname(__FILE__).'/common_site.php';
