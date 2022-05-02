<?php

namespace elements;

trait Taggable
{
    protected ?string $class = null;

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    protected function tag(string $data, string $divisor = "span"): string {
        if ($this->class !== null) {
            return "<$divisor class=\"$this->class\">$data</$divisor>";
        } else {
            return "<$divisor>$data</$divisor>";
        }
    }
}