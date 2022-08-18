<?php

namespace elements\bootstrap;

use elements\IElement;

class Button implements \elements\IElement
{
    private string $url;
    private IElement $element;
    private string $type;

    /**
     * @param string $url
     * @param IElement $element
     * @param string $type
     */
    public function __construct(string $url, IElement $element, string $type = "primary")
    {
        $this->url = $url;
        $this->element = $element;
        $this->type = '-'.$type;
    }

    function build(): string
    {
        $text = $this->element->build();
        return <<<END
<a class="btn btn$this->type" href="$this->url" role="button">$text</a>
END;

    }
}