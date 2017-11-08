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
    PHPDOC\Component\Metric,
    PHPDOC\Document\Writer\Exception\SaveException
    ;

/**
 * Creates properties for sections <w:sectPr>
 */
class SectionFormatter extends Shared
{

    /**
     * Property aliases
     */
    private static $aliases = array(
        'page'          => 'pgSz',
        'pageSize'      => 'pgSz',
        'pgsz'          => 'pgSz',
        'grid'          => 'docGrid',
        'valign'        => 'vAlign',
        'margin'        => 'pgMar',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'bidi'                  => 'bool',
            'cols'                  => 'columns',
            'docGrid'               => 'grid',
            'formProt'              => 'bool',
            'lnNumType'             => 'linenum',
            'pgBorders'             => 'border',
            'pgMar'                 => 'margin',
            'pgNumType'             => 'pagenum',
            'pgSz'                  => 'pagesize',
            'rtlGutter'             => 'bool',
            'textDirection'         => 'text',
            'type'                  => 'type',
            'vAlign'                => 'text'
        ) + $this->map;
    }

    protected function process_margin($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('top', 'right', 'bottom', 'left',
                              'header', 'footer', 'gutter');
        static $aliases = array(
        );

        // assume a margin width is being set for all sides
        if (!is_array($val)) {
            $val = array(
                'top' => $val,
                'right' => $val,
                'bottom' => $val,
                'left' => $val,
                'header' => $val / 2,
                'footer' => $val / 2,
                'gutter' => 0,
            );
        }

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        foreach ($val as $key => $v) {
            $attr = $this->lookupAlias($key, $aliases);
            if (!in_array($attr, $attrs)) {
                continue;
            }

            $v = Metric::make($v, Metric::UNIT_IN)->convertTo(Metric::UNIT_DXA);
            $prop->appendChild(new \DOMAttr('w:' . $attr, $v));
        }

        $root->appendChild($prop);

        return true;
    }

    /**
     * Process page size
     */
    protected function process_pagesize($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('w', 'h', 'orient', 'code');
        static $aliases = array(
            'width'       => 'w',
            'height'      => 'h',
            'orientation' => 'orient'
        );

        // assume the orientation is being set
        if (!is_array($val)) {
            $val = array( 'orient' => $val );
            if ($val['orient'] == 'landscape-booklet') {
                $val['w'] = 14;
                $val['h'] = 8.5;
            } else if ($val['orient'] == 'landscape') {
                $val['w'] = 11;
                $val['h'] = 8.5;
            } else {
                $val['w'] = 8.5;
                $val['h'] = 11;
            }
        }

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        foreach ($val as $key => $v) {
            $attr = $this->lookupAlias($key, $aliases);
            if (!in_array($attr, $attrs)) {
                continue;
            }

            if ($attr == 'w' or $attr == 'h') {
                $v = Metric::make($v, Metric::UNIT_IN)->convertTo(Metric::UNIT_DXA);
            }

            $prop->appendChild(new \DOMAttr('w:' . $attr, $v));
        }

        $root->appendChild($prop);

        return true;
    }

    /**
     * Process section type
     */
    protected function process_type($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $valid = array(
            'continuous', 'evenPage', 'oddPage', 'nextPage', 'nextColumn',
        );

        // allow shortcuts for pages, "next" => "nextPage", etc...
        if (in_array($val, array('even','odd','next'))) {
            $val .= 'Page';
        }

        if (!in_array($val, $valid)) {
            throw new SaveException("Invalid type value \"$val\". Must be one of: " . implode(',',$valid));
        }

        return $this->appendSimpleValue($root, $name, $val);
    }

	protected function process_columns($name, $val, ElementInterface $element, \DOMNode $root)
	{
		if (!is_array($val)) $val = array(
			'num' => $val,
			'equalWidth' => true
		);
		if ($val['num'] <= 1) return true;
		if ($val['space']) $val['space'] = Metric::make($val['space'])->convertTo(Metric::UNIT_DXA);

		$dom = $root->ownerDocument;
		$node = $dom->createElement('w:cols');
		$this->setAttributesToNode(array_diff_key($val, array('col'=>1)), $node, 'w:');

		if (isset($val['col']) and is_array($val['col'])) foreach ($val['col'] as $col) {
			$colNode = $dom->createElement('w:col');
			$this->setAttributesToNode($colNode, array(
					'w' => Metric::make($col['width'])->converTo(Metric::UNIT_DXA),
					'space' => Metric::make($col['space'])->converTo(Metric::UNIT_DXA)
				), 'w:');
			$node->appendChild($colNode);
		}
		
		$root->appendChild($node);
		return true;
	}
}
