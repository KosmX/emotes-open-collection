<?php

namespace elements;

class LiteralElement implements IElement
{
    private string $literal;

    function __construct(string $str) {
        $this->literal = $str;
    }

    function build(): string
    {
        return $this->literal;
    }
}


