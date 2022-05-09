<?php

namespace elements;

class ErrorTag extends AbstractSimplestElement
{
    function build(): string
    {
        $p = parent::build();
        return <<<END
<div class="alert alert-danger">$p</div>
END;

    }
}