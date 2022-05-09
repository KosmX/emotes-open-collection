<?php declare(strict_types=1);

namespace elements\bootstrap\navbar;


class LiteralEntry implements IEntry {
    private string $mode = '';
    private string $literal;
    private string $target;

    function __construct(string $l, string $t) {
        $this->literal = $l;
        $this->target = $t;
    }

    function toStr(): string
    {
        $extra = '';
        if ($this->mode != '') {
            $extra = " $this->mode";
        }

        return <<<END
        <li class="nav-item">
          <a class="nav-link$extra" href="\\$this->target">$this->literal</a>
        </li>
END;

    }

    function getName(): string
    {
        return $this->target;
    }

    function setCurrent(): void
    {
        $this->mode = 'active';
    }

    function disable(): void
    {
        $this->mode = 'disabled';
    }
}