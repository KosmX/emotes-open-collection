<?php

namespace elements\form;

use elements\AbstractSimplestElement;

class Fieldset extends AbstractSimplestElement
{
    function build(): string
    {
        $p = parent::build();
        return "<fieldset>$p</fieldset>";
    }
}