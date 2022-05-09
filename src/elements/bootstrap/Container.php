<?php

namespace elements\bootstrap;

use elements\IElement;

class Container extends \elements\AbstractSimplestElement
{
    public string $size = '';

    public function __construct(IElement $element, string $size = '')
    {
        $this->size = $size;
        parent::__construct($element);
    }


    public function build(): string
    {
        $add = '';
        if ($this->size != '') {
            $add = "-$this->size";
        }
        $p = parent::build();

        return <<<DIV
<div class="container$add">$p</div>
DIV;

    }

    public static function getDefaultSpacer(IElement $l): Container {
        return new \elements\bootstrap\Container($l, 'xl d-flex justify-content-center mt-3');
    }
}