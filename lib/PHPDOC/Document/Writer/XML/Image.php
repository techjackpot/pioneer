<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer\XML;

use PHPDOC\Document\WriterInterface,
    PHPDOC\Element\ElementInterface;

abstract class Image
{
    public static function process(WriterInterface $writer, \DOMNode $root, ElementInterface $element)
    {
        $node = $writer->getDom()->createElement('img');
        $root->appendChild($node);

        $node->appendChild(new \DOMAttr('src', $element->getSource()));

        if ($element->hasProperties()) {
            foreach ($element->getProperties() as $key => $val) {
                $node->appendChild(new \DOMAttr($key, $val));
            }
        }
    }
}
