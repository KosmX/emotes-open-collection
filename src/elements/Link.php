<?php

namespace elements;

class Link extends AbstractSimplestElement
{
    private string $url;

    /**
     * @param IElement $element
     * @param string $url
     */
    public function __construct(IElement $element, string $url)
    {
        parent::__construct($element);
        $this->url = $url;
    }

    function build(): string
    {
        $p = parent::build();
        return "<a href='$this->url'>$p</a>";
    }
}