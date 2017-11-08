<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Document\Writer\Word2007\Formatter;

use PHPDOC\Element\ElementInterface,
    PHPDOC\Document\Writer\Exception\SaveException,
    PHPDOC\Component\Metric,
    PHPDOC\Element\Table
    ;

/**
 * Creates properties for Table <w:tbl>
 */
class TableFormatter extends Shared
{

    /**
     * Property aliases
     */
    private static $aliases = array(
        'align'     => 'jc',
        'justify'   => 'jc',
        'width'     => 'tblW',
        'border'    => 'tblBorders',
        'bgColor'   => 'shd',
        'shading'   => 'shd',
        'indent'    => 'tblInd',
        'margin'    => 'tblCellMar',
        'spacing'   => 'tblCellSpacing',
        'layout'    => 'tblLayout',
        'style'		=> 'tblStyle',
        'look'		=> 'tblLook'
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'bidiVisual'            => '',
            'jc'                    => 'align',
            'shd'                   => 'shading',
            'tblBorders'            => 'border',
            'tblCellMar'            => 'margin',
            'tblCellSpacing'        => 'tblSpacing',
            'tblInd'                => 'tblIndent',
            'tblLayout'             => 'tblLayout',
            'tblLook'               => 'look',
            'tblOverlap'            => '',
            'tblpPr'                => '',
            'tblStyle'              => 'text',
            'tblStyleColBandSize'   => 'text',
            'tblStyleRowBandSize'   => 'text',
            'tblW'                  => 'tblWidth',
        ) + $this->map;
    }

    protected function process_tblLayout($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $valid = array('autofit', 'fixed');

        if ($val == 'auto') {
            $val = 'autofit';
        }
        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid \"$name\" value \"$val\". Must be one of: " . implode(',',$valid));
        }

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);
        $prop->appendChild(new \DOMAttr('w:type', $val));

        $root->appendChild($prop);
        return true;
    }

    /**
     * Process border property
     */
    protected function process_border($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $sides = array('top', 'right', 'bottom', 'left',
                              'insideH', 'insideV');
        static $attrs = array('val', 'color', 'themeColor', 'themeTint',
                              'themeShade', 'sz', 'space', 'shadow', 'frame');

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        // If $val is a string then copy its value to each border side
        if (!is_array($val)) {
            // @todo make this smarter
            $val = array(
                'top' => $val,
                'right' => $val,
                'bottom' => $val,
                'left' => $val,
                'insideV' => $val,
                'insideH' => $val
            );
        }

        foreach ($val as $side => $bdr) {
            if (!in_array($side, $sides)) {
                continue;
            }

            $node = $dom->createElement('w:' . $side);
            $prop->appendChild($node);

			if ($bdr instanceof Matric) $bdr = array('sz' => $bdr, 'val'=>'single');
			elseif (!is_array($bdr)) $bdr = array('sz' => Metrict::make($bdr, Metrict::UNIT_PT), 'val'=>'single');

			if (!isset($bdr['sz'])) $brd['sz'] = new Metric(null);
			if (!($bdr['sz'] instanceof Metric)) $bdr['sz'] = new Metric($bdr['sz'], Metric::UNIT_PT);
			if ($bdr['sz']->isNull()) {
				$bdr = array('val'=>'nil');
			} else $bdr['sz'] = $bdr['sz']->convertTo(Metric::UNIT_PT)*8;

			if (isset($bdr['space'])) {
				if (!($bdr['space'] instanceof Metric)) $bdr['space'] = new Metric($bdr['space'], Metric::UNIT_PT);
				$bdr['space'] = $bdr['space']->convertTo(Metric::UNIT_PT);
			}
			if (!isset($bdr['val'])) $bdr['val'] = 'single';

            foreach ($bdr as $k => $v) {
                if (!in_array($k, $attrs)) {
                    continue;
                }

                if ($k == 'shadow' or $k == 'frame') {
                    $v = $this->getOnOff($v);
                }

                $node->appendChild(new \DOMAttr('w:' . $k, $v));
            }
        }

        $root->appendChild($prop);
        return true;
    }

	protected function process_margin($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('top', 'right', 'bottom', 'left');
        static $aliases = array(
        );

        // assume a margin width is being set for all sides
        if (!is_array($val)) {
            $val = array(
                'top' => $val,
                'right' => $val,
                'bottom' => $val,
                'left' => $val
            );
        }

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        foreach ($val as $key => $v) {
            $attr = $this->lookupAlias($key, $aliases);
            if (!in_array($attr, $attrs)) {
                continue;
            }

            $v = Metric::make($v, Metric::UNIT_IN);
            $v->convertTo(Metric::UNIT_DXA);
            $node = $dom->createElement('w:' . $attr);
            $node->appendChild(new \DOMAttr('w:type', $v->getUnit()));
            $node->appendChild(new \DOMAttr('w:w', $v->getValue()));
            $prop->appendChild($node);
        }

        $root->appendChild($prop);

        return true;
    }

	protected function process_look($name, $val, ElementInterface $element, \DOMNode $root)
	{
		static $maskMap = array(
			'firstColumn' => Table::LOOK_FIRST_COL, 'firstRow' => Table::LOOK_FIRST_ROW, 'lastColumn' => Table::LOOK_LAST_COL,
			'noHBand' => Table::LOOK_NO_HBAND, 'noVBand' => Table::LOOK_NO_VBAND
		);
		$val = (int) $val;
		$r = array();
		foreach ($maskMap as $key=>$flag)
			$r[$key] = (int) !!($val&$flag);

		$r['val'] = sprintf('%04x', $val);
		$node = $root->ownerDocument->createElement('w:' . $name);
		$this->setAttributesToNode($r, $node, 'w:');
		$root->appendChild($node);

		return true;
	}
}
