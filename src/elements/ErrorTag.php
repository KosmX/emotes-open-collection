<?php

namespace elements;

class ErrorTag extends AbstractSimplestElement
{
    function build(): string
    {
        $p = parent::build();
        return <<<END
<div style="border: chocolate solid 2px; border-radius: 12px;background: coral; font-size: x-large; margin-left: auto; margin-right: auto; width: fit-content; padding: 8px">$p</div>
END;

    }
}