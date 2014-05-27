<?php
namespace Goetas\Twital\Helper;

/**
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 *
 */
class RecursiveDOMIterator implements \RecursiveIterator
{
    private $position;
    private $nodeList;

    public function __construct(\DOMNode $domNode)
    {
        $this->position = 0;
        $this->nodeList = $domNode->childNodes;
    }

    public function current()
    {
        return $this->nodeList->item($this->position);
    }

    public function getChildren()
    {
        return new static($this->current());
    }

    public function hasChildren()
    {
        return $this->current()->hasChildNodes();
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->position < $this->nodeList->length;
    }
}
