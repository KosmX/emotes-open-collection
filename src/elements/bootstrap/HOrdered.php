<?php

namespace elements\bootstrap;

use elements\IElement;
use elements\ListTrait;
use elements\Taggable;

class HOrdered implements IElement
{
    use ListTrait;

    public int $gapSize = 3;


    public function addElement(IElement $element, int $size = 6):void {
        $this->elements[] = array($element, $size);
    }

    function build(): string
    {
        $ret = '';
        foreach ($this->getElements() as $element) {
            $e = $element[0]->build();
            $ret .= "<div class='col-sm-$element[1]'>$e</div>";
        }

        return <<<END
<div class="container mt-$this->gapSize">
<div class="row justify-content-between">
$ret
</div>
</div>
END;

    }
}