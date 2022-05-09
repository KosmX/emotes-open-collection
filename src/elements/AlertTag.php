<?php

namespace elements;

class AlertTag extends AbstractSimplestElement
{

    public string $type;

    /**
     * @param IElement $element
     * @param string $type
     */
    public function __construct(IElement $element, string $type = 'alert-danger')
    {
        parent::__construct($element);
        $this->type = $type;
    }


    function build(): string
    {
        $p = parent::build();
        return <<<END
<div class="alert $this->type">$p</div>
END;

    }
}