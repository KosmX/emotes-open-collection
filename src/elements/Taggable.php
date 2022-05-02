<?php

namespace elements;

trait Taggable
{
    protected string $class;

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    protected function tag(string $data, string $divisor = "span"): string {
        return "<$divisor class=\"$this->class\">$data</$divisor>";
    }
}