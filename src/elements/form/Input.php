<?php

namespace elements\form;

use elements\IElement;

class Input implements IElement
{
    private string $type;
    private string $name;
    private string $value;

    /**
     * @param string $type
     * @param string $name
     * @param string $value
     */
    public function __construct(string $type, string $name, string $value)
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }


    function build(): string
    {
        return "<input type='$this->type' name='$this->name' value='$this->value'>";
    }
}