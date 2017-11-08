<?php 

class Enum {
	protected static $__enums = array();
	protected $__vals = array();
	protected $__index = array();

	public static function getEnum($obj, $enum_name) {
		if (is_object($obj)) $class = get_class($obj);
		else $class = $obj;
		if (!isset(self::$__enums[$class.$enum_name])) {
			$name = strtoupper($enum_name);
			if ($name[strlen($name)-1] != '_') $name .= '_';
			$ref = new ReflectionClass($obj);
			$con = $ref->getConstants();
			if (!is_array($con)) $con = array();
			$construct = array();
			foreach ($con  as $cn=>$cv) {
				if (strpos($cn, $name) === 0) $construct[substr($cn, strlen($name))] = $cv;
			}
			self::$__enums[$class.$enum_name] = new Enum($construct);
		}
		return self::$__enums[$class.$enum_name];
	}
	
	public function __construct() {
		$args = func_get_args();
		if (count($args) == 1) $args = array_pop($args);
		$index = 0;
		foreach ($args as $varname=>$value) {
			$this->__vals[$varname] = $value;
			$this->__index[$varname] = $index++;
		}
	}

	public function getAll() { return $this->__vals; }

	public function compare($first, $second) {
		if (!array_key_exists(strtoupper($first), $this->__index)) {
			$first = array_search($first, $this->__vals);
			if ($first === false) return false;
		} else $first = strtoupper($first);
		if (!array_key_exists(strtoupper($second), $this->__index)) {
			$second = array_search($second, $this->__vals);
			if ($second === false) return false;
		} else  $second = strtoupper($second);
		$f = $this->__index[$first];
		$s = $this->__index[$second];
		return ($f-$s)*-1;
	}
	
	public function val($v) {
		if (!array_key_exists(strtoupper($v), $this->__index)) {
			$v = array_search($v, $this->__vals);
			if ($v === false) return false;
		} else $v = strtoupper($v);
		return $this->__vals[$v];
	}
	
	public function key($v) {
		if (!array_key_exists(strtoupper($v), $this->__index)) {
			$v = array_search($v, $this->__vals);
			if ($v === false) return false;
		} else $v = strtoupper($v);
		return $v;
	}
	
	public function compareBits($first, $second) {
		if (array_key_exists(strtoupper($first), $this->__index)) {
			$first = $this->__vals[strtoupper($first)];
		}
		if (array_key_exists(strtoupper($second), $this->__index)) {
			$second = $this->__vals[strtoupper($second)];
		}
		return (($first&$second)!=0);
	}
	
	public function has($name, $keyOnly = false) {
		if (!array_key_exists(strtoupper($name), $this->__vals)) {
			if ($keyOnly) return false;
			else return (array_search($name, $this->__vals) === false)?(false):(true); 
		} return true;
	}
	
	public function __get($varname) {
		$varname = strtoupper($varname);
		if (array_key_exists($varname, $this->__vals)) return $this->__vals[$varname];
		return null;
	}
}