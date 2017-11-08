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
 * Creates properties for paragraphs <w:p>
 */
class ParagraphFormatter extends Shared
{
    /**
     * Property aliases
     */
    private static $aliases = array(
        'align'     => 'jc',
        'justify'   => 'jc',
        'border'    => 'pBdr',
        'indent'    => 'ind',
        'outline'   => 'outlineLvl',
        'style'     => 'pStyle',
        'bgColor'   => 'shd',
        'shading'   => 'shd',
        //'font'      => 'pFonts',
    );

    protected function initMap()
    {
        parent::initMap(self::$aliases);
        $this->map = array(
            'adjustRightInd'        => 'bool',
            'autoSpaceDE'           => 'bool',
            'autoSpaceDN'           => 'bool',
            'bidi'                  => 'bool',
            'contextualSpacing'     => 'bool',
            'ind'                   => 'indent',
            'jc'                    => 'align',
            'keepLines'             => 'bool',
            'keepNext'              => 'bool',
            'kinsoku'               => 'bool',
            'mirrorIndents'         => 'bool',
            'numPr'                 => 'numbering',
            'outlineLvl'            => 'decimal',
            'overflowPunct'         => 'bool',
            'pageBreakBefore'       => 'bool',
            'pBdr'                  => 'border',
            'pFonts'                => 'font',
            'pStyle'                => 'text',
            'rPr'                   => 'run',
            'shd'                   => 'shading',
            'snapToGrid'            => 'bool',
            'spacing'               => 'spacing',
            'suppressAutoHyphens'   => 'bool',
            'suppressLineNumbers'   => 'bool',
            'suppressOverlap'       => 'bool',
            'tabs'                  => 'tabs',
            'textAlignment'         => 'valign',
            'textboxTightWrap'      => 'textwrap',
            'textDirection'         => 'textdir',
            'topLinePunct'          => 'bool',
            'widowControl'          => 'bool',
            'wordWrap'              => 'bool',
        ) + $this->map;
    }

    protected function process_numbering($name, $val, ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        if (!is_array($val)) {
            $val = array(
                'ilvl' => 0,
                'numId' => $val ?: 0,
            );
        }

        foreach ($val as $k => $v) {
            $this->appendSimpleValue($prop, $k, $v);
        }

        $root->appendChild($prop);
        return true;
    }

    /**
     * Process spacing
     */
    protected function process_spacing($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $attrs = array('after', 'afterAutospacing', 'afterLines',
                              'before', 'beforeAutospacing', 'beforeLines',
                              'line', 'lineRule');

        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        // if $val is a string then assume its a simple spacing value for
        // 'before' and 'after'.
        if (!is_array($val)) {
            $val = array(
                'after' => $v,
                'before' => $v,
                'line' => 240,  // 240ths of a line == 1 line
                'lineRule' => 'auto',
            );
        }
        if (isset($val['after'])) $val['after'] = Metric::make($val['after'], Metric::UNIT_PT)->convertTo(Metric::UNIT_DXA);
        if (isset($val['before'])) $val['before'] = Metric::make($val['before'], Metric::UNIT_PT)->convertTo(Metric::UNIT_DXA);
        if (isset($val['beforeLines'])) $val['beforeLines'] = Metric::make($val['beforeLines'], Metric::UNIT_PT)->convertTo(Metric::UNIT_HP);
        if (isset($val['afterLines'])) $val['afterLines'] = Metric::make($val['afterLines'], Metric::UNIT_PT)->convertTo(Metric::UNIT_HP);

        foreach ($val as $k => $v) {
            if (!in_array($k, $attrs)) {
                continue;
            }

            $prop->appendChild(new \DOMAttr('w:' . $k, $v));
        }

        $root->appendChild($prop);
        return true;
    }

    /**
     * Process border property
     */
    protected function process_border($name, $val, ElementInterface $element, \DOMNode $root)
    {
        static $sides = array('top', 'right', 'bottom', 'left', 'between', 'bar');
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
            );
        }

        foreach ($val as $side => $bdr) {
            if (!in_array($side, $sides)) {
                continue;
            }

            $node = $dom->createElement('w:' . $side);
            $prop->appendChild($node);

            // @todo make this smarter
            if (!is_array($bdr)) {
                $bdr = array( 'sz' => $bdr );
            }

            // WordML requires the 'val' attribute to be present
            if (!isset($bdr['val'])) {
                $bdr['val'] = 'single';
            }

            foreach ($bdr as $k => $v) {
                if (!in_array($k, $attrs)) {
                    continue;
                }

                if ($k == 'sz') {
                    $v = $v * 8;               // Eighths of a point
                } elseif ($k == 'shadow') {
                    $v = $this->getOnOff($v);
                }

                $node->appendChild(new \DOMAttr('w:' . $k, $v));
            }
        }

        $root->appendChild($prop);
        return true;
    }

    protected function process_indent($name, $val, ElementInterface $element, \DOMNode $root)
    {
        $dom = $root->ownerDocument;
        $prop = $dom->createElement('w:' . $name);

        // If $val is a string then copy its value to left and right
        if (!is_array($val)) {
            // @todo make this smarter
            $val = array(
                'left' => $val,
                'right' => $val
            );
        }

        // @todo <w:ind> has other properties available to implement

        foreach ($val as $k => $v) {
            $prop->appendChild(new \DOMAttr('w:'.$k, Metric::make($v, Metric::UNIT_IN)->convertTo(Metric::UNIT_DXA)));
        }

        $root->appendChild($prop);
        return true;
    }

}
