<?php

namespace elements;

class SimpleTable implements IElement
{
    use TableTrait;
    public ?string $class = null;

    function build(): string
    {
        return $this->buildTable($this->class);
    }
}