<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

$query = unslash($_GET['q']);
$table = unslash($_GET['table']);

$valid_tables = [ 'vendors_services', 'interests', 'sectors', 'regions', 'billing_types', 'short_titles' ];

if (!in_array($table, $valid_tables, true)) { error_404(); }

if (blank($q)) {
	$ret = sqlla("SELECT name FROM `$table` ORDER BY name");
}
else {
	$ret = sqlla("SELECT name FROM `$table` WHERE name LIKE %s ORDER BY name LIMIT 10", $query.'%');
}

json_response($ret);
