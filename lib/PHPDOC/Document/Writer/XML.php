<?php
/**
 * This file is part of the PHP Open Doc library.
 *
 * @author Jason Morriss <lifo101@gmail.com>
 * @since  1.0
 * 
 */
namespace PHPDOC\Document\Writer;

use PHPDOC\Document,
    PHPDOC\Document\WriterInterface,
    PHPDOC\Document\Writer\Exception\SaveException,
    PHPDOC\Element\ElementInterface,
    PHPDOC\Element\SectionInterface
    ;

/**
 * Writer\XML is a class to convert a Document into a basic XML tree.
 *
 * This class produces a basic XML tree based on the Document given. One use
 * for this class is to help with testing and debugging a Document structure.
 */
class XML implements WriterInterface 
{
    /**
     * @var boolean If true SaveException will be thrown if any errors occur
     *              during Document processing, otherwise an E_USER_* errors
     *              will be triggered.
     */
    protected $throwSaveException;

    /**
     * @var Document The document to process.
     */
    
    protected $document;

    /**
     * @var \DOMDocument The resulting DOM object.
     */
    protected $dom;

    /**
     * @var array Class paths to check for element processors
     */
    protected $classPaths;

    /**
     * Construct a new instance
     *
     * @param Document The document to process. Optional.
     */
    public function __construct(Document $document = null)
    {
        $this->document = $document;
        $this->throwSaveException = true;
        $this->classPaths = array( get_class($this), get_class() );
    }

    /**
     * Saves the document as an XML string.
     *
     * @param string   $output   Output filename or other stream.
     * @param Document $document Document to process. Optional only if a
     *                           document was given to the constructor.
     */
    public function save($output = null, Document $document = null)
    {
        if ($document == null) {
            if ($this->document === null) {
                if ($this->throwSaveException) {
                    throw new SaveException("No Document defined");
                } else {
                    trigger_error("No Document defined", E_USER_ERROR);
                    return $this;
                }
            }
            $document = $this->document;
        }
        
        $this->processDocument($document);
        $this->saveDom($output);
        
        return $this;
    }

    /**
     * Saves the DOM as XML
     *
     * @param string   $output   Output filename or other stream.
     */
    protected function saveDom($output = null)
    {
        if ($output === null) {
            $output = 'php://output';
        }

        $fp = @fopen($output, 'wb');
        if (!$fp) {
            if ($this->throwSaveException) {
                throw new SaveException("Error opening output \"$output\" for writing");
            } else {
                trigger_error("Error opening output \"$output\" for writing", E_USER_ERROR);
                return $this;
            }
        }
        fwrite($fp, $this->dom->saveXML());
        fclose($fp);
        
        return $this;
    }
    
    /**
     * Set a new DOMDocument object.
     *
     * Creates a new DOMDocument object. The caller can pass in a new object
     * of their own creation, or pass nothing and a default object will be
     * created instead.
     *
     * @param \DOMDocument $dom Optional new DOMDocument object
     */
    public function setDom(\DOMDocument $dom = null)
    {
        if ($dom === null) {
            $dom = new \DOMDocument('1.0', 'utf-8');
            $dom->formatOutput = true;
        }
        $this->dom = $dom;
    }
    
    /**
     * Returns the current DOM object.
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * Shortcut so caller doesn't have to instantiate a new XML() object to
     * save the document.
     *
     * @param Document $document The document to save
     * @param string   $output   The output filename or stream
     */
    public static function saveDocument(Document $document, $output = null)
    {
        $writer = new XML();
        return $writer->save($output, $document);
    }

    /**
     * Create the DOM from the Document given
     *
     * @param Document $document The Document to process
     */
    public function processDocument(Document $document)
    {
        if (!$this->dom) {
            $this->setDom();
        }

        // process main Document
        if ($document instanceof Document) {
            $root = $this->dom->createElement('document');
            $this->dom->appendChild($root);

            $body = $this->dom->createElement('sections');
            $root->appendChild($body);
            
            foreach ($document->getSections() as $section) {
                $this->processSection($body, $section);
            }
            
        } else {
            // process sub node within the document
            $root = $document;
        }
        
        return $this->dom;
    }
    
    /**
     * Process an individual section block
     *
     * @param \DOMNode         $root    The DOM node to attach elements to.
     * @param SectionInterface $section The Section to process
     */
    public function processSection(\DOMNode $root, SectionInterface $section)
    {
        $node = $this->dom->createElement('section');
        $node->appendChild(new \DOMAttr('name', $section->getName()));
        $root->appendChild($node);

        if ($section->hasProperties()) {
            foreach ($section->getProperties() as $key => $val) {
                $node->appendChild(new \DOMAttr($key, $val));
            }
        }

        $headers = $this->dom->createElement('headers');
        foreach ($section->getHeaders() as $h) {
            // don't bother processing it if it has no elements 
            if ($h->hasElements()) {
                $head = $this->dom->createElement('hdr');
                $head->appendChild(new \DOMAttr('type', $h->getType()));
                if ($h->hasProperties()) {
                    foreach ($h->getProperties() as $key => $val) {
                        $head->appendChild(new \DOMAttr($key, $val));
                    }
                }
                $headers->appendChild($head);
                $this->processChildren($head, $h);
            }
        }
        if ($headers->hasChildNodes()) {
            $node->appendChild($headers);
        }

        $footers = $this->dom->createElement('footers');
        foreach ($section->getFooters() as $h) {
            // don't bother processing it if it has no elements 
            if ($h->hasElements()) {
                $foot = $this->dom->createElement('ftr');
                $foot->appendChild(new \DOMAttr('type', $h->getType()));
                if ($h->hasProperties()) {
                    foreach ($h->getProperties() as $key => $val) {
                        $foot->appendChild(new \DOMAttr($key, $val));
                    }
                }
                $footers->appendChild($foot);
                $this->processChildren($foot, $h);
            }
        }
        if ($footers->hasChildNodes()) {
            $node->appendChild($footers);
        }
        
        $content = $this->dom->createElement('body');
        $node->appendChild($content);

        $this->processChildren($content, $section);
    }

    /**
     * Process an element's children
     *
     * @param \DOMNode         $root    The DOM node to attach elements to.
     * @param ElementInterface $element The element to process
     */
    public function processChildren(\DOMNode $root, $element)
    {
        // process all children within the element ...
        foreach ($element->getElements() as $child) {
            $this->processElement($root, $child);
        }
    }

    /**
     * Process a single element
     * 
     * @param \DOMNode         $root    The DOM node to attach elements to.
     * @param ElementInterface $element The element to process
     */
    public function processElement(\DOMNode $root, $element)
    {
        // determine the base name of the element (without namespace)
        $name = get_class($element);
        $name = substr($name, strrpos($name, '\\')+1);

        // have processor class process the element
        $processed = false;
        foreach ($this->classPaths as $class) {
            $className = $class . '\\' . $name;
            if (class_exists($className)) {
                $className::process($this, $root, $element);
                $processed = true;
                break;
            }
        }

        if (!$processed) {
            if ($this->throwSaveException) {
                throw new SaveException("Element processor for \"$name\" not found in \"$className\"");
            } else {
                trigger_error("Element processor for \"$name\" not found in \"$className\"", E_USER_WARNING);
                return;
            }
        }
    }

    /**
     * Configures the writer to throw SaveException on errors.
     *
     * If false trigger_error() is called instead.
     *
     * @param bool $value New value.
     */
    public function setSaveException($value)
    {
        $this->throwSaveException = (bool)$value;
        return $this;
    }
}