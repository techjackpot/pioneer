<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 *
 */
namespace PHPDOC\Element;

use PHPDOC\Property\Properties,
	PHPDOC\Component\Metric
	;

/**
 *
 * @example
 * <code>
    $img = new Watermark('path/to/image.png', array(...properties...));
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Tsanyo Tsanev <tsanyo@dzineit.net>
 */
class Watermark extends Image {

	public function getShapeStyle() {
		$props = array(
			'position:absolute',
			'mso-position-horizontal-relative:margin',
			'mso-position-vertical-relative:page'
		);
		$props[] = 'z-index:'.(($this->properties->has('z-index'))?(bcadd('-251657216', $this->properties->get('z-index'))):('-251657216'));
		$props[] = 'mso-position-horizontal:'.(($this->properties->has('position-horizontal'))?($this->properties->get('position-horizontal')):('center'));
		$props[] = 'mso-position-vertical:'.(($this->properties->has('position-vertical'))?($this->properties->get('position-vertical')):('top'));
		$props[] = 'margin:'.implode(' ', Metric::parseTRBL(($this->properties->has('margin'))?($this->properties->get('margin')):(0)));
		return implode(';', $props).';'.parent::getShapeStyle();
	}
}
