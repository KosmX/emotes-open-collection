<?php

namespace elements;

class AbstractSimplestElement implements IElement
{
    private IElement $element;

    /**
     * @param IElement $element
     */
    public function __construct(IElement $element)
    {
        $this->element = $element;
    }

    function build(): string
    {
        return $this->element->build();
    }
}