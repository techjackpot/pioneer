<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_once __DIR__.'/Validation.php';

abstract class Model implements ArrayAccess, Countable, IteratorAggregate {
	use Validation;
	
	public $_initial = array();
	public $_cached = array();
	public $data = array();
	public $errors = array();
	public static $table = null;
	public static $properties = ['updated_at' => ['label' => 'Modified']];
	public static $search_fields = null;
	public static $name_field = '%s.name';
	public static $parent_model =  null;
	public static $human_name =  null;
	
	public function __construct($id = null, $cast = null) {
		if ( is_null($id) ) {
			$this->apply( static::defaults() );
		}
		elseif ( is_string($id) || is_int($id) ) {
			$data = sqla( (static::select_sql().static::join_sql()." WHERE `".static::$table."`.`id` = %s"), $id ) or $data = array();
			$this->_cached = self::extract_cached($data);
			$this->cast_data($data);
			$this->data = $data;
			$this->_initial = $data;
		}
		elseif ( is_array($id) ) {
			$this->_cached = self::extract_cached($id);
			if ($cast) $this->cast_data($id);
			$this->data = $id;
			$this->_initial = $id;
		}
	}
	
	public function cast_data(&$array) {
		foreach ($array as $key => &$value) {
			$this->cast($key, $value);
		}
	}
	
	public static function extract_cached(&$data) {
		$ret = array();
		foreach($data as $key => $val) {
			if ($key[0] === '_') {
				$ret[substr($key, 1)] = $val;
				unset($data[$key]);
			}
		}
		return $ret;
	}
	
	public static function human_name() {
		return static::$human_name === null ? get_called_class() : static::$human_name;
	}
	public static function human_names() {
		return static::human_name().'s';
	}
	
	public static function name_field($table = null) {
		if ($table === null) { $table = static::$table; }
		return sprintf( static::$name_field, "`$table`" );
	}
	
	public static function get($id) {
		$data = sqla( (static::select_sql().static::join_sql()." WHERE ".static::$table.".`id` = %s"), $id );
		if ($data) {
			return new static($data, true);
		}
		else return null;
	}
	
	public static function select_sql() {
		$q = "SELECT `".static::$table."`.*";
		foreach(static::select_fields() as $key => $value) {
			$q .= ", ($value) `$key`";
		}
		foreach(static::join_fields() as $value) {
			$q .= ", $value";
		}
		$q .= " FROM `".static::$table."`";
		return $q;
	}
	
	public static function select_fields() {
		return [];
		$ret = array();
		foreach(static::$properties as $key => $props) {
			if ($foreign = $props['foreign'] and $props['load']) {
				$ret["_$key"] = "SELECT ".$foreign::name_field("_$key")." FROM `".$foreign::$table."` `_$key` WHERE `_$key`.`id` = `".static::$table."`.`$key`";
			}
		}
		return $ret;
	}
	
	public static function where_sql($where) {
		if (is_array($where)) {
			$where2 = array();
			foreach($where as $key => $val) { 
				if (is_array($val)) { $where2[] = '`'.static::$table."`.`$key` IN (".implode(',', array_map('sanitize', $val)).")"; }
				else if (is_null($val)) { $where2[] = '`'.static::$table."`.`$key` IS NULL"; }
				else { $where2[] = '`'.static::$table."`.`$key` = ".sanitize($val); }
			}
			$where = implode(' AND ', $where2);
		}
		return $where;
	}
	
	public static function join_fields() {
		$ret = [];
		foreach(static::$properties as $key => $props) {
			if ($foreign = $props['foreign'] and $props['load']) {
				$ret[] = $foreign::name_field("_$key")." `_$key`";
			}
		}
		return $ret;
	}
	
	public static function join_tables() {
		$ret = [];
		foreach(static::$properties as $key => $props) {
			if ($foreign = $props['foreign'] and $props['load']) {
				$ret[] = "LEFT JOIN `".$foreign::$table."` `_$key` ON `_$key`.`id` = `".static::$table."`.`$key`";
			}
		}
		return $ret;
	}
	
	public static function join_sql() {
		return " ".implode(' ', static::join_tables() )." ";
	}
	
	public static function find($opts = array()) {
		extract($opts);
		$q = static::select_sql();
		
		$where = static::where_sql($where);
		
		$join = static::join_sql();
		
		$q .= $join;
		
		if ($search) {
			$search = preg_replace('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', ' ',str_replace("\"", "", str_replace("'", "", sanitize($search))));
			/*if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', sanitize($search)))
			{
				//$bestmatch = preg_replace('/(\w+)/', '+$1', $search);
				$bestmatch = preg_replace('/[^\da-z]/i', ' ', $search);
				//$search = "'".$bestmatch."' IN BOOLEAN MODE";
				$search = "'".$bestmatch."' IN NATURAL LANGUAGE MODE";
				
			}
			else
			{*/
				$bestmatch = preg_replace('/(\w+)/', '+$1', $search);
				$search = "'(".$bestmatch."*) (\"".$search."\")' IN BOOLEAN MODE";
				//$search = "'(".$bestmatch.") (".$search.")' IN NATURAL LANGUAGE MODE";
			//}
			$q .= " INNER JOIN search_index ON ( search_index.model_name = '".static::$table."' AND search_index.model_id = `".static::$table."`.id) WHERE MATCH(search_index.name, search_index.contents) AGAINST (".$search.")";
			if ($where) $q .= " AND ($where)";
			
			if ($group) { $q .= " GROUP BY $group"; }
			if ($sort != "updated_at DESC") { $q .= " ORDER BY $sort";
			}
			else {
			$q .= " ORDER BY MATCH(search_index.name, search_index.contents) AGAINST (".$search.") DESC";
			}
			$sort = "";
			
		}
		else if ($where) { $q .= " WHERE ($where)"; }
		if (!$search && $group) { $q .= " GROUP BY $group"; }
		if (!$search && $sort) { $q .= " ORDER BY $sort"; }
		if (!$search && $limit) { $q .= " LIMIT $limit "; }
		if ($total) {
			$q2 = "SELECT count(*) FROM `".static::$table."`";
			$q2 .= $join;
			if ($search) {
				$q2 .= " INNER JOIN search_index ON ( search_index.model_name = '".static::$table."' AND search_index.model_id = `".static::$table."`.id) WHERE MATCH(search_index.name, search_index.contents) AGAINST (".$search.")";
				if ($where) $q2 .= " AND ($where)";
			}
			else if ($where) { $q2 .= " WHERE ($where)"; }
			$total = (int) sqlr($q2);
		}
		else $total = null;
		
		return new ModelIterator(get_called_class(), $q, $total);
	}
	
	public static function where($where, $other = array()) {
		return static::find(['where' => $where] + $other);
	}
	
	public static function first($where, $other = array()) {
		$iter = static::find(['where' => $where] + $other);
		return $iter->current();
	}
	
	public static function find_names($where = null, $opts = array(), $extra_fields = null) {
		extract($opts);
		$q = "SELECT `".static::$table."`.id".(!$concat ? ", ".static::name_field() : "");
		if (!blank($extra_fields) and $concat) { $q .= ", ".(is_array($extra_fields) ? "CONCAT_WS(' : ',".implode(', ', $extra_fields) : $extra_fields).") as title"; }
		else if (!blank($extra_fields)) { $q .= ", ".(is_array($extra_fields) ? implode(', ', $extra_fields) : $extra_fields); }
		$q .= " FROM `".static::$table."`";
		if ($join) { $q .= " ".$join." "; }
		$where = static::where_sql($where);
		if ($where) { $q .= " WHERE $where"; }
		if ($sort) { $q .= " ORDER BY $sort"; }
		if ($limit) { $q .= " LIMIT $limit "; }
		
		return sql($q);
	}
	
	public static function display_name($id) {
		if (blank($id)) return;
		else if (is_array($id)) return  sqlr("SELECT GROUP_CONCAT(".static::name_field()." SEPARATOR ', ') FROM `".static::$table."` WHERE id in (".implode(',', array_map('sanitize', $id)).")");
		else return sqlr("SELECT ".static::name_field()." FROM `".static::$table."` WHERE id = %s", $id);
	}
	
	public static function display_with_id($id) {
		if (blank($id)) return;
		else if (is_array($id))
		{
			$res = sqlaa("SELECT `id`, ".static::name_field()." FROM `".static::$table."` WHERE id in (".implode(',', array_map('sanitize', $id)).")");
			foreach ($res as $value)
			{
				$return[$value['id']] = $value['name'];
			}
		}
		else
		{
			$return = sqla("SELECT `id`, ".static::name_field()." FROM `".static::$table."` WHERE id = %s", $id);
			$return[$value['id']] = $value['name'];
		}
		return $return;
	}
	
	public static function &defaults() {
		$ret = array();
		foreach(static::$properties as $key => $props) {
			if (array_key_exists('default', $props)) {
				$ret[$key] = $props['default'];
			}
		}
		return $ret;
	}
	
	public static function excluded_keys() {
		$ret = [];
		foreach(static::$properties as $key => $prop) {
			if ($prop['virtual']) { $ret[] = $key; }
		}
		return $ret;
	}
	
	public function load_externals() {
		foreach(static::$properties as $index => $props) {
			if ($props['external'] && !array_key_exists($index, $this->data)) {
				$this->offsetSet($index, $this->getProp($index), false);
			}
		}
	}
	
	public function apply($data) {
		foreach ($data as $index=>$value) {
			$this[$index] = $value;
		}
	}
	
	public function offsetExists($index) {
		return array_key_exists($index, $this->data) || (array_key_exists($index, static::$properties) && static::$properties[$index]['external']);
	}
	
	public function &offsetGet($index) {
		if ( !array_key_exists($index, $this->data) && array_key_exists($index, static::$properties) && static::$properties[$index]['external']) {
			$this->offsetSet($index, $this->getProp($index), false);
		}
		return array_key_exists($index, $this->data) ? $this->data[$index] : null;
	}
	
	public function offsetSet($index, $newval, $process = true) {
		if ($process) {
			if (!array_key_exists($index, $this->_initial)) {
				if (static::$properties[$index]['external'] && !array_key_exists($index, $this->_initial)) $this->_initial[$index] = $this->getProp($index);
			}
			$this->process($index, $newval);
		}
		else {
			$this->_initial[$index] = $newval;
		}
		unset($this->_cached[$index]);
		$this->data[$index] = $newval;
	}
	
	public function offsetUnset($index) {
		if (array_key_exists($index, $this->_initial)) {
			$this->offsetSet($index, $this->_initial[$index], false);
			unset($this->_cached[$index]);
		}
		else unset($this->data[$index]);
	}
	
	public function count() {
		return count($this->data);
	}
	
	public function getIterator() {
		return new ArrayIterator($this->data);
	}
	
	public function validate() {
		foreach($this::$properties as $key => $prop) {
			if ($prop['required']) {
				$this->validate_required($key);
			}
		}
	}
	
	public function __get($key) {
		$prop = $this::$properties["{$key}_id"];
		if ( $prop && ($class = $prop['foreign']) ) {
			$this->$key = $this["{$key}_id"] === null ? null : new $class($this["{$key}_id"]);
			return $this->$key;
		}
		else return null;
	}
	
	public function save($changelog = true) {
	//public function save($changelog = false) {
		$this->before_save();
		if ($this['created_at']) {
			$this->before_update();
			$this->load_externals();
			$id = array_key_exists('id', $this->_initial) ? $this->_initial['id'] : $this['id'];
		}
		else {
			$this->before_create();
			$this['created_at'] = gmdate('Y-m-d H:i:s');
		}
		$this['updated_at'] = gmdate('Y-m-d H:i:s');
		
		$data = array();
		$dirty = array();
		foreach($this->data as $key => $value) {
			if ( !(array_key_exists($key, static::$properties) && (static::$properties[$key]['virtual'] || static::$properties[$key]['external']) ) && $this->_initial[$key] !== $this[$key] ) { 
				$data[$key] = $this[$key];
				$this->uncast( $key, $data[$key] );
				$dirty[$key] = $this->_initial[$key];
			}
		}
		if ($id) {
			$status = mysql_update_row(static::$table, $id, $data);
			if ($status === false) { return false; }
		}
		else {
			$status = mysql_insert_into(static::$table, $data);
			if ($status === false) { return false; }
			if ($status > 0) { $this->offsetSet('id', $status, false); }
		}
		foreach(static::$properties as $key => $props) {
			if ($props['external'] && array_key_exists($key, $this->data) && $this->_initial[$key] !== $this[$key] ) {
				$dirty[$key] = $this->_initial[$key];
				$this->setProp($key);
			}
		}
		$this->update_search_index();
		if ($id) {
			if ($changelog == true)
			ChangeLog::update($this, $dirty);
			$this->after_save();
			$this->after_update();
		}
		else {
			if ($changelog == true)
			ChangeLog::create($this);
			$this->after_save();
			$this->after_create();
		}
		$this->_initial = $this->data;
		return true;
	}
	
	public function delete() {
		$this->before_delete();
		$this->load_externals();
		$status = sql("DELETE FROM `".static::$table."` WHERE `id` = %s", $this['id']);
		if ($status) {
			foreach(static::$properties as $key => $props) {
				if ($props['external']) {
					$func = "{$key}_delete";
					if (is_callable(array($this, $func))) { $this->$func(); }
				}
			}
			$this->delete_search_index();
			ChangeLog::delete($this);
			
			$this->after_delete();
		}
		return $status;
	}
	
	public function update_search_index() {
		if (!$this::$search_fields) { return; }
		
		$contents = array();
		
		foreach($this::$search_fields as $val) { $contents[] = $this->display($val); }
		
		sql("INSERT INTO `search_index` (`model_name`, `model_id`, `name`, `contents`) VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `contents`=VALUES(`contents`)", static::$table, $this['id'], $this->name(), implode(' | ', array_filter($contents)));
	}
	
	public function delete_search_index() {
		sql("DELETE FROM `search_index` WHERE `model_name` = %s AND `model_id` = %s", static::$table, $this['id']);
	}
	
	public static function create($data) {
		$model = new static();
		$model->apply( $data );
		if ($model->save()) { return $model; }
		else { return false; }
	}
	
	public static function properties($name, $key = null) {
		if ($key !== null) return array_key_exists($name, static::$properties) ? static::$properties[$name][$key] : null;
		else return static::$properties[$name];
	}
	
	public static function label($name) {
		$properties = static::$properties[$name];
		if ($properties && array_key_exists('label', $properties)) { return $properties['label']; }
		else { return preg_replace('/ id(s?)$/i', '\1', ucwords(str_replace('_',' ', $name))); }
	}
	
	public function name_from_label($label) {
		$label = strtolower($label);
		foreach(static::$properties as $key => $props) {
			if ($props['label'] && strtolower($props['label']) === $label) {
				return $key;
			}
		}
		foreach($this->data as $key => $value) {
			if (strtolower($this->label($key)) === $label) {
				return $key;
			}
		}
	}
	
	public function getProp($key) {
		$external = $this::$properties[$key]['external'];
		
		if ( $external && $external !== true && !is_callable(array($this, "{$key}_get"))  ) {
			$foreign = $this::$properties[$key]['foreign'];
			return many_to_many($this::$table, $this['id'], $foreign::$table, $external);
		}
		else {
			return $this->{"{$key}_get"}();
		}
	}
	
	public function setProp($key) {
		$external = $this::$properties[$key]['external'];
		
		if ( $external && $external !== true && !is_callable(array($this, "{$key}_set"))  ) {
			$foreign = $this::$properties[$key]['foreign'];
			return update_many_to_many($this::$table, $this['id'], $foreign::$table, $this[$key], $external);
		}
		else {
			return $this->{"{$key}_set"}();
		}
	}
	
	public function cast($key, &$value) {
		$func = "{$key}_cast";
		$cast = array_key_exists($key, static::$properties) ? static::$properties[$key]['cast'] : null;
		if (is_callable([$this, $func])) {
			$value = $this->$func($value);
		}
		else if ($value !== null) {
			if ($cast === 'int') {
				$value = (int) $value;
			}
			else if ($cast === 'float') {
				$value = (float) $value;
			}
			else if ($cast === 'bool') {
				$value = $value ? true : false;
			}
			else if (key_is_id($key)) {
				$value = to_int($value);
				if (is_array($value)) { sort($value, SORT_NUMERIC); }
			}
		}
	}
	
	public function uncast($key, &$value) {
		$func = "{$key}_uncast";
		$cast = array_key_exists($key, static::$properties) ? static::$properties[$key]['cast'] : null;
		if (is_callable([$this, $func])) {
			$value = $this->$func($value);
		}
		else if ($value !== null) {
			if ($cast === 'bool') {
				$value = $value ? 1 : 0;
			}
		}
	}
	
	public function process($key, &$value) {
		$func = "{$key}_process";
		if (array_key_exists($key, static::$properties)) {
			$process = array_key_exists('process', static::$properties[$key]) ? static::$properties[$key]['process'] : static::$properties[$key]['cast'];
		}
		
		if (is_callable([$this, $func])) {
			$value = $this->$func($value);
		}
		else if ($value !== null) {
			if ($process === 'int') {
				$value = (int) $value;
			}
			else if ($process === 'float') {
				$value = (float) $value;
			}
			else if ($process === 'bool') {
				$value = $value ? true : false;
			}
			else if ($process === 'datetime') {
				$time = strtotime($value);
				if ($time) {
					$value = date('Y-m-d H:i:s', $time);
				}
			}
			else if ($process === 'date') {
				$time = strtotime($value);
				if ($time) {
					$value = date('Y-m-d', $time);
				}
			}
			else if ($process === 'time') {
				$time = strtotime($value);
				if ($time) {
					$value = date('H:i:s', $time);
				}
			}
			else if ($process === 'money') {
				$value = number_format(floatval(str_replace(',', '', $value)), 2, '.', '');
			}
		}
	}
	
	public function values($key) {
		if (is_array($this::$properties['values'])) {
			return $this::$properties['values'];
		}
		else if ( array_key_exists($key, $this::$properties) and $foreign = $this::$properties[$key]['foreign'] and !is_callable(array($this, "{$key}_values")) )  {
			if ($scope = $this::$properties[$key]['scope']) { $scope = sprintf($scope, $this['id']); }
			return $foreign::find_names($scope);
		}
		else {
			return $this->{"{$key}_values"}();
		}
	}
	
	public function display_changelog($key) {
		if (is_callable([$this, "{$key}_changelog"])) {
			return $this->{"{$key}_changelog"}();
		}
		else {
			return $this->display($key);
		}
	}
	
	public function display($key) {
		$properties = static::$properties[$key];

		if (array_key_exists($key, $this->_cached)) {
			return $this->_cached[$key];
		}
		else if ($display = $properties['display']) {
			if ($display === true) {
				return $this->{"{$key}_display"}();
			}
			else if ($this[$key] === null) {
				return null;
			}
			else if ($display === 'bool') {
				return $this[$key] ? 'Yes' : 'No';
			}
			else if ($display === 'datetime') {
				return date('n/j/Y g:ia', strtotime($this[$key]));
			}
			else if ($display === 'date') {
				return date('n/j/Y', strtotime($this[$key]));
			}
			else if ($display === 'time') {
				return date('g:ia', strtotime($this[$key]));
			}
			else if ($display === 'money') {
				return '$'.number_format($this[$key], 2);
			}
			else {
				return $this[$key];
			}
		}
		elseif (is_callable(array($this, "{$key}_display"))){
			return $this->{"{$key}_display"}();
		}
		elseif (is_array($properties['values'])) {
			if ($properties['storestring'] == true) {
				return $this[$key];
			}
			else
			{
				return $properties['values'][$this[$key]];
			}
		}
		elseif ($properties['foreign']) {
			$this->_cached[$key] = $properties['foreign']::display_name($this[$key]);
			//$this[$key] = $properties['foreign']::display_with_id($this[$key]);
			//var_dump($this[$key]);
			return $this->_cached[$key];
			//return $this[$key];
		}
		else {
			return $this[$key];
		}
	}
	
	public function updated_at_display() {
		return format_dt( $this['updated_at'] );
	}
	
	public function created_at_display() {
		return format_dt( $this['created_at'] );
	}
	
	public function name() {
		$field = substr($this::$name_field, 3);
		return $this[ $field ] === null ? '' : $this[ $field ];
	}
	
	// Callbacks
	public function before_save() { }
	public function before_create() { }
	public function before_update() { }
	public function before_delete() { }
	public function after_save() { }
	public function after_create() { }
	public function after_update() { }
	public function after_delete() { }
	public function after_comment() { }
	
}

class ModelIterator implements Iterator, Countable {
	public $position;
	public $data;
	public $class;
	public $total;
	public $_current;
	
	public function __construct($class, $data, $total = null) {
		$this->position = 0;
		$this->_current = null;
		$this->class = $class;
		$this->data = is_string($data) ? sql($data) : $data;
		$this->total = $total;
	}
	
	public function __destruct() {
		if ($this->data) { $this->data->free(); }
	}

   public function rewind() {
	   $this->position = 0;
		$this->_current = null;
   }

   public function current() {
		if ($this->position >= $this->data->num_rows || $this->position < 0) return;
		if ($this->_current === null) {
			$this->data->data_seek($this->position);
		   $this->_current = new $this->class($this->data->fetch_assoc(), true);
		}
		return $this->_current;
	}

   public function key() {
	   return $this->position;
   }

   public function next() {
		$this->position++;
		$this->_current = null;
   }

   public function valid() {
	   return $this->position >= 0 && $this->position < $this->data->num_rows;
   }
	
	public function count() {
		return $this->data->num_rows;
	}
	
	public function total() {
		return $this->total;
	}
	
}
