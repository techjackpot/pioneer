<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

function sanitize($value) {
	global $mysql;
	if ($value === null) return 'NULL';
	else return "'".$mysql->real_escape_string((string)$value)."'";
}

function escape_column($value) {
	return "`$value`";
}

function sql($query) {
	global $mysql;
	if (func_num_args() > 1) {
		$query = vsprintf($query, array_map('sanitize', array_slice(func_get_args(), 1)) );
	}
	//if ($_SERVER['REMOTE_ADDR'] == "173.63.120.230") echo $query."<br><br>\n";
	// information_message(h($query));
	$q = $mysql->query($query);
	if (!$q) {
		//information_message(h($query)); error_message('MySQL Error: %s', htmlspecialchars($mysql->error));
		echo "<hr />".h($query)."<br />".htmlspecialchars($mysql->error)."<hr /><br /><br />";
		}
	return $q;
}

function sqlr($query) {
	$q = call_user_func_array('sql', func_get_args());
	if (!$q || $q->num_rows === 0) return null;
	else {
		list($ret) = $q->fetch_array(MYSQLI_NUM);
		return $ret;
	}
}
function sqla($query) {
	$q = call_user_func_array('sql', func_get_args());
	if (!$q || $q->num_rows === 0) return null;
	else return $q->fetch_array(MYSQLI_ASSOC);
}

function sqll($query) {
	$q = call_user_func_array('sql', func_get_args());
	if (!$q ||  $q->num_rows == 0) return null;
	else return $q->fetch_array(MYSQLI_NUM);
}

function &sqlaa($q) {
	if (!($q instanceof mysqli_result)) {
		$q = call_user_func_array('sql', func_get_args());
	}
	
	$ret = array();
	while( $ret[] = $q->fetch_assoc() or array_pop($ret) ) { }
	
	return $ret;
}
function &sqlla($q) {
	if (!($q instanceof mysqli_result)) {
		$q = call_user_func_array('sql', func_get_args());
	}
	
	$ret = array();
	while( list($id, $val) = $q->fetch_row() ) {
		if ($q->field_count === 1) { $ret[] = $id; }
		else { $ret[$id] = $val; }
	}
	
	return $ret;
}

function mysql_insert_into($table, $array) {
	global $mysql;
	$result = sql("INSERT INTO `$table` (".implode(', ', array_map('escape_column', array_keys($array))).") VALUES (".implode(', ', array_map('sanitize', array_values($array))).")");
	
	if ($result) return $mysql->insert_id;
	else return false;
}
function mysql_update_row($table, $id, $array) {
	global $mysql;
	foreach($array as $key => $value) {
		if (!isset($q)) { $q = ""; }
		else { $q .= ", "; }
		$q .= "`".$key."` = ".sanitize($value);
	}
	$result = sql("UPDATE `$table` SET ".$q." WHERE `id` = ".sanitize($id));
	
	if ($result) return (int) $id;
	else return false;
}

function next_sort_id($table, $column = 'sort') {
	return (int) sqlr("SELECT COALESCE(`$column`, 0)+1 FROM `$table` ORDER BY `$column` DESC LIMIT 1");
}

function many_to_many($first_table, $first_id, $second_table, $join_table = null) {
	if ($first_id === null) return;
	$ret = array();
	
	if (is_null($join_table)) $join_table = $first_table < $second_table ? "{$first_table}_{$second_table}" : "{$second_table}_{$first_table}";
	$first_key = substr($first_table, 0, -1).'_id';
	$second_key = substr($second_table, 0, -1).'_id';
		
	$sql = sql("SELECT `$second_key` FROM `$join_table` WHERE `$first_key` = %s ORDER BY `$second_key`", $first_id);
	while(list($id) = $sql->fetch_row()) { $ret[] = (int) $id; }
	return presence($ret);
}


function update_many_to_many($first_table, $first_id, $second_table, $values, $join_table = null) {
	$ret = array();
	
	if (is_null($join_table)) $join_table = $first_table < $second_table ? "{$first_table}_{$second_table}" : "{$second_table}_{$first_table}";
	$first_key = substr($first_table, 0, -1).'_id';
	$second_key = substr($second_table, 0, -1).'_id';
	$sql = sql("DELETE FROM `$join_table` WHERE `$first_key` = %s", $first_id);
	
	if (is_array($values)) {
		foreach($values as $value) {
			sql("INSERT INTO `$join_table` (`$first_key`, `$second_key`) VALUES (%s, %s)", $first_id, $value);
		}
	}
}

function text_to_many($table, $text, $column = 'name') {
	$ids = [];
	$words = preg_split('/\s*,\s*/', squash($text));

	foreach($words as $word) {
		if ($word === '') { continue; }
		$word_id = sqlr("SELECT `id` FROM `$table` WHERE lower(`$column`) = lower(%s)", $word);
		if (!$word_id) {
			$word_id = mysql_insert_into($table, [ $column => $word ]);
		}
		$ids[] = (int) $word_id;
	}
	
	sort($ids);
	return presence(array_unique($ids, SORT_NUMERIC));
}
