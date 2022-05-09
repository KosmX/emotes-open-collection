<?php

namespace elements;

trait ListTrait
{

    private array $elements = array();

    public function addElement(IElement $element):void {
        $this->elements[] = $element;
    }

    protected function getElements(): array
    {
        return $this->elements;
    }
}