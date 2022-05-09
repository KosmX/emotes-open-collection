<?php

namespace elements\bootstrap\navbar;

use elements\ListTrait;

class DropdownEntry implements IEntry
{

    private string $mode = '';
    private string $literal;
    private string $name;

    private array $elements = array();

    function __construct(string $l, string $dropdownName) {
        $this->literal = $l;
        $this->name = $dropdownName;
    }

    public function addEntry(string $target, string $text) {
        $this->elements[] = array($target, $text);
    }

    function toStr(): string
    {

        $b = '';
        foreach ($this->elements as $element) {
            if ($element == null) {
                $b .= '<li><hr class="dropdown-divider"></li>';
            } else {
                $b .= "<li><a class=\"dropdown-item\" href=\"$element[0]\">$element[1]</a></li>\n";
            }
        }

        return <<<END
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle$this->mode" href="#" id="{$this->name}Dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">$this->literal</a>
          <ul class="dropdown-menu" aria-labelledby="{$this->name}Dropdown">
          $b
          </ul>
        </li>
END;

    }

    function getName(): string
    {
        return $this->name;
    }

    function setCurrent(): void
    {
        $this->mode .= ' active';
    }

    function disable(): void
    {
        $this->mode .= ' disabled';
    }

    public function addSeparator()
    {
        $this->elements[] = null;
    }
}