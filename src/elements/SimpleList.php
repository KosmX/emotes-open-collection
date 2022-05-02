<?php

namespace elements;

class SimpleList implements IElement
{
    use ListTrait;
    use Taggable;

    function build(): string
    {
        $ret = '';
        foreach ($this->getElements() as $element) {
            $ret .= $element->build();
        }
        return $this->tag($ret);
    }
}