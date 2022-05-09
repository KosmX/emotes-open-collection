<?php

namespace elements;

class SubmitConstantButton extends AbstractSimplestElement
{
    private array $entries;
    private string $method;
    private string $action;

    /**
     * @param array $entries
     * @param string $method
     * @param string $action
     */
    public function __construct(IElement $element, array $entries, string $method, string $action)
    {
        parent::__construct($element);
        $this->entries = $entries;
        $this->method = $method;
        $this->action = $action;
    }

    function build(): string
    {
        $inputs = '';
        foreach ($this->entries as $key=>$entry) {
            $inputs .= "<input type='hidden' name='$key' value='$entry'>";
        }
        $p = parent::build();
        return "<form action='$this->action' method='$this->method'> $inputs <button type='submit'> $p </button> </form>";
    }
}