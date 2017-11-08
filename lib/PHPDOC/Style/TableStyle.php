<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Tsanyo Tsanev  <tsanyo@dzineit.net>
 * @since  1.0
 *
 */
namespace PHPDOC\Style;

/**
 * The ParagraphStyle class represents a paragraph style definition.
 *
 * @version 1.0
 * @since 1.0
 * @author Tsanyo Tsanev  <tsanyo@dzineit.net>
 */
use PHPDOC\Property\Properties,
    PHPDOC\Property\PropertiesInterface;

class TableStyle extends \PHPDOC\Style implements TableStyleInterface
{
	const TYPE_ROW_ODD = 'band1Horz';
	const TYPE_COL_ODD = 'band1Vert';
	const TYPE_ROW_EVEN = 'band2Horz';
	const TYPE_COL_EVEN = 'band2Vert';
	const TYPE_FIRST_COL = 'firstCol';
	const TYPE_FIRST_ROW = 'firstRow';
	const TYPE_LAST_COL = 'lastCol';
	const TYPE_LAST_ROW = 'lastRow';
	const TYPE_NE_CELL = 'neCell';
	const TYPE_NW_CELL = 'nwCell';
	const TYPE_SE_CELL = 'seCell';
	const TYPE_SW_CELL = 'swCell';
	const TYPE_DEFAULT = 'wholeTable';

	protected $moreStyles = array();

	public function getSubStyle($type)
	{
		if (self::TYPE_DEFAULT == $type) return $this->getProperties();
		elseif (isset($this->moreStyles[$type])) return $this->moreStyles[$type];
		return null;
	}

	public function setSubStyle($type, $key, $value) {
		if ($type === self::TYPE_DEFAULT) return $this->properties->set($key, $value);
		if (!isset($this->moreStyles[$type])) $this->moreStyles[$type] = new Properties();
		return $this->moreStyles[$type]->set($key, $value);
	}

	public function setSubStyles($type, $sub) {
		if (!is_array($sub) and !($sub instanceof PropertiesInterface))
			throw new \InvalidArgumentException('The second argument of setSubStyles() must be an array or instance of PropertiesInterface', 1);
		if (!isset($this->moreStyles[$type])) $this->moreStyles[$type] = new Properties();
		$this->moreStyles[$type]->merge($sub);
		return $this;
	}

	public function getAllStyles() {
		return array(self::TYPE_DEFAULT => $this->properties) + $this->moreStyles;
	}
}
