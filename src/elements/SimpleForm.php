<?php

namespace elements;

class SimpleForm extends SimpleList
{
    private string $method;
    private string $action;

    public function __construct(string $method, string $action)
    {
        $this->action = $action;
        $this->method = $method;
    }

    function build(): string
    {
        $elements = parent::build();
        return "<form method='$this->method' action='$this->action'>$elements</form>";
    }
}