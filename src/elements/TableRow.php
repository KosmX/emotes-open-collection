<?php

namespace elements;

class TableRow implements IElement
{
    use ListTrait;

    function build(): string
    {
        $row = "";
        foreach ($this->getElements() as $element) {
            $e = $element->build();
            $row .= "<td>$e</td>\n";
        }
        return $row;
    }
}