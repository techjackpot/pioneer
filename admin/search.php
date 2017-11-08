<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

if (!is_logged() || user_type() <= User::OFFICIAL)
error_404();

$query = unslash($_POST['q']);

$ret = array();

if ($path[2] === 'batch') {
	$search = MailingList::search_for_condition($path[3], $query);
	foreach( $search as $item ) { $ret[$item['id']] = $item->name(); }
}
else if ($path[2] === 'users') {
	$class = 'User';
	$search = $class::find(array('where' => ($class::name_field()." LIKE ".sanitize("%{$query}%")), 'limit' => 10));
	foreach( $search as $item ) { $ret[$item['id']] = $item->name(); }
}
else if ($path[2] === 'company') {
	$class = 'Organization';
	$search = $class::find(array('where' => ($class::name_field()." LIKE ".sanitize("%{$query}%")), 'limit' => 10));
	foreach( $search as $item ) { $ret[$item['id']] = $item->name(); }
}
if (!$search) { error_404(); }

echo json_encode(array( 'q' => $query, 'results' => $ret ));
