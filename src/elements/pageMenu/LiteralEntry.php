<?php

namespace elements\pageMenu;


class LiteralEntry implements IEntry {
    private bool $isCurrent = false;
    private string $literal;
    private string $target;

    function __construct(string $l, string $t) {
        $this->literal = $l;
        $this->target = $t;
    }

    function toStr(): string
    {
        if ($this->isCurrent) {
            return "<li class='navbarActive'><a href=\"\\$this->target\">$this->literal</a></li>";
        } else {
            return "<li><a href=\"\\$this->target\">$this->literal</a></li>";
        }
    }

    function getName(): string
    {
        return $this->target;
    }

    function setCurrent(): void
    {
        $this->isCurrent = true;
    }
}