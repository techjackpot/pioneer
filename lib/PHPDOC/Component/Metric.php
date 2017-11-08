<?php
namespace PHPDOC\Component;

class Metric {
	const UNIT_PX = 'px';
	const UNIT_IN = 'in';
	const UNIT_PT = 'pt';
	const UNIT_CM = 'cm';
	const UNIT_DXA = 'dxa';
	const UNIT_HP = 'hp';
	const UNIT_MM = 'mm';
	const UNIT_EMU = 'emu';
	const UNIT_PCT = 'pct';
	const UNIT_NULL = 'nil';
	const UNIT_AUTO = 'auto';

	const CM_PER_INCH   = 2.54;
	const EMU_PER_INCH  = 914400;
	const EMU_PER_CM = 360000;

	public static $DPI  = 72;

	protected $value = 0;
	protected $unit = 'px';
	protected $isRelative = false;

	public static function parseTRBL($value) {
		if (is_numeric($value)) {
			$value = array($value, $value);
		} elseif (is_string($value)) {
			$value = preg_split("/\s+/", $value);
		}
		if (!isset($value[1])) $value[1] = $value[0];
		if (!isset($value[2])) $value[2] = $value[0];
		if (!isset($value[3])) $value[3] = $value[1];
		return array(
			new Metric($value[0]),
			new Metric($value[1]),
			new Metric($value[2]),
			new Metric($value[3])
		);
	}

	public static function setDPI($dpi) {
		self::$DPI = $dpi;
	}

	public static function make($n, $u=null) {
		if ($n instanceof static) return $n;
		return new static($n, $u);
	}

	public function __construct($n, $unit = null) {
		if ($n === null or $n === 'null' or $n === 'nil') {
			$this->value = 0;
			$this->unit = self::UNIT_NULL;
			return;
		}
		if (preg_match("/^([0-9]+(?:\.[0-9]+)?)\s*([A-z]+|%)?$/", $n, $m)) list(,$n,$u) = $m;
		if ($u === null) $u = $unit;
		if ($u === null) $u = self::UNIT_PX;
		if ($u === '%') $u = self::UNIT_PCT;
		if (!\Enum::getEnum($this, 'UNIT')->has($u)) throw new \UnexpectedValueException('Unsupporeted unit for metric: '.$u);
		if ($u === self::UNIT_NULL) $n = 0;
		$this->value = (float)$n;
		$this->unit = $u;
		$this->isRelative = $this->isUnitRelative();
		if ($unit !== null and $u != $unit) try {
			$this->convertTo($unit);
		} catch (\UnexpectedValueException $e) {
		
		}
	}

	public function isUnitRelative($unit=null) {
		if ($unit === null) $unit = $this->unit;
		return ($unit == self::UNIT_PCT);
	}

	public function isNull() { return $this->unit == self::UNIT_NULL; }

	public function getUnit() {
		return $this->unit;
	}

	public function getValue() {
		return $this->value;
	}

	public function getValueForXML() {
		if ($this->unit == self::UNIT_PCT) return sprintf('%d', $this->value*50);
		return $this->value;
	}

	public function __toString() {
		if ($this->unit == self::UNIT_NULL) return 'nil';
		elseif ($this->unit == self::UNIT_AUTO) return 'auto';
		else return sprintf('%.3f%s', $this->value, $this->unit);	
	}

	public function getAs($unit) {
		$u = $this->unit; $n = $this->value;
		$r = $this->convertTo($unit);
		$this->unit = $u; $this->value = $n;
		return $r;
	}

	public function convertTo($unit, Metric $rel = null) {
		if ($unit == self::UNIT_NULL or $this->unit == self::UNIT_NULL) return 0;
		if ($unit == self::UNIT_AUTO or $this->unit == self::UNIT_AUTO) return 0;
		if ($this->unit === $unit) return $this->value;
		if (!\Enum::getEnum($this, 'UNIT')->has($unit)) throw new \UnexpectedValueException('Unsupported convert unit '.$unit);
		if (($this->isRelative xor $this->isUnitRelative($unit)) and (!$rel or $rel->isUnitRelative()))
			throw new \UnexpectedValueException('Can not convert to or from relative units without passing another (non-relative) metric.');
		if ($this->isRelative xor $this->isUnitRelative($unit)) {
			if ($this->isRelative) {
				$this->value = ($this->_toPct()/100)*$rel->getAs(self::UNIT_PT);
				$this->unit = self::UNIT_PT;
				$this->isRelative = false;
			} else {
				$this->value = $this->_toPt()/$rel->getAs(self::UNIT_PT)*100;
				$this->unit = self::UNIT_PCT;
				$this->isRelative = true;
			}
		}
		if ($this->unit == self::UNIT_PCT) $method = '_toPct';
		else $method = '_to'.$unit;
		$this->value = $this->{$method}();
		$this->unit = $unit;
		return $this->value;
	}

	protected function _toPct() {
		if ($this->unit == self::UNIT_PCT) return $this->value;
		return 100;
	}

	protected function _toPx() {
		if ($this->unit == self::UNIT_PX) return $this->value;
		return $this->_toIn()*self::$DPI;
	}

	protected function _toIn() {
		if ($this->unit == self::UNIT_IN) return $this->value;
		return $this->_toCm()/self::CM_PER_INCH;
	}

	protected function _toPt() {
		if ($this->unit == self::UNIT_PT) return $this->value;
		return ($this->_toPx()*self::$DPI)/72;
	}

	protected function _toCm() {
		if ($this->unit == self::UNIT_CM) return $this->value;
		return $this->_toEmu()/self::EMU_PER_CM;
	}

	protected function _toDxa() {
		if ($this->unit == self::UNIT_DXA) return $this->value;
		return $this->_toHp()*10;
	}

	protected function _toHp() {
		if ($this->unit == self::UNIT_HP) return $this->value;
		return $this->_toPt()*2;
	}

	protected function _toMm() {
		if ($this->unit == self::UNIT_MM) return $this->value;
		return (($this->_toDxa()/20)/72)*self::CM_PER_INCH*10;
	}

	protected function _toEmu() {
		if ($this->unit == self::UNIT_EMU) return $this->value;
		return ($this->_toMm()/10)*self::EMU_PER_CM;
	}
}
