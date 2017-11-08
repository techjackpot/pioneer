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
 * The Image element class represents a single image.
 *
 * @example
 * <code>
    $img = new Image('path/to/image.png', array(...properties...));
 * </code>
 *
 * @version 1.0
 * @since 1.0
 * @author Jason Morriss  <lifo101@gmail.com>
 */
class Image extends Element implements ImageInterface
{
    /**
     * @var string Source path of the image
     */
    protected $source;

    /**
     * @var Internal cache to store Image information
     * @internal
     */
    private $cache;

    /**
     * Instantiate a new Image object.
     *
     * @param string $source     Source path of the image.
     * @param mixed  $properties Custom properties for the image.
     */
    public function __construct($source, $properties = null)
    {
        parent::__construct($properties);
        $this->setSource($source);
    }

    /**
     * {@inheritdoc}
     */
    public function save($dest)
    {
        if (!@copy($this->getSource(), $dest)) {
            $err = error_get_last();
            throw new ElementException("Error saving image to file \"$dest\": " . $err['message']);
        }
    }

    /**
     * Update the image cache
     * @internal
     */
    private function updateCache()
    {
        $cache = @getimagesize($this->source);
        if (!$cache) {
            throw new ElementException("Invalid image. Unable to fetch image metadata.");
        }
        $this->cache = array(
            'width' => new Metric($cache[0], Metric::UNIT_PX),
            'height' => new Metric($cache[1], Metric::UNIT_PX),
            'type' => $cache[2],
            'mime' => $cache['mime'],
            'bits' => $cache['bits'],
            'channels' => $cache['channels']
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getWidth($allowOverride = false)
    {
        if ($allowOverride and $this->properties->has('width')) {
            $val = $this->properties->get('width');
            if (!($val instanceof Metric)) $val = new Metric($val);
            return $val;
        }

        // @codeCoverageIgnoreStart
        if (!$this->cache) {
            $this->updateCache();
        }
        // @codeCoverageIgnoreEnd
        return $this->cache['width'];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight($allowOverride = false)
    {
        if ($allowOverride and $this->properties->has('height')) {
            $val = $this->properties->get('height');
            if (!($val instanceof Metric)) $val = new Metric($val);
            return $val;
        }

        // @codeCoverageIgnoreStart
        if (!$this->cache) {
            $this->updateCache();
        }
        // @codeCoverageIgnoreEnd
        return $this->cache['height'];
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        // @codeCoverageIgnoreStart
        if (!$this->cache) {
            $this->updateCache();
        }
        // @codeCoverageIgnoreEnd
        return $this->cache['mime'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        // @codeCoverageIgnoreStart
        if (!$this->cache) {
            $this->updateCache();
        }
        // @codeCoverageIgnoreEnd
        $ext = substr($this->cache['mime'], strrpos($this->cache['mime'], '/')+1);
        if ($ext == 'jpeg') {
            $ext = 'jpg';
        }
        return $ext;
    }

    public function getData()
    {
        // @codeCoverageIgnoreStart
        if (!$this->cache) {
            $this->updateCache();
        }
        // @codeCoverageIgnoreEnd

        if ($this->isFile()) {
            return file_get_contents($this->source);

        } elseif (substr($this->source, 0, 5) == 'data:') {
            list($proto, $data) = explode(',', $this->source, 2);
            list($mime, $enc) = explode(';', substr($proto, 5), 2);
            $decode = $enc . '_decode';
            return $decode($data);
        }

        // @todo refactor getData so this is not needed.
        // @codeCoverageIgnoreStart
        throw new ElementException("Unknown error fetching image data");
        // @codeCoverageIgnoreEnd
    }

    public function isFile()
    {
        return substr($this->source, 0, 5) != 'data:';
    }

    public function isRemoteFile()
    {
        $pos = strpos($this->source, '://');
        // if there is no protocol then its a normal local file path
        if ($pos === false) {
            return false;
        }

        $proto = substr($this->source, 0, $pos);
        // A lot more protocol wrappers could be considered 'local' but its
        // safer to assume everything else is remote.
        return $proto != 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

	public function getShapeStyle() {
		return sprintf('width:%s;height:%s;',
            $this->getWidth(true),
            $this->getHeight(true)
        );
	}

    /**
     * {@inheritdoc}
     */
    public function setSource($source)
    {
        $this->source = $source;
        $this->cache = null;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hasElements()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getElements()
    {
        return array();
    }
}
