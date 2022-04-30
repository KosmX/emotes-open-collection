<?php declare(strict_types=1);

namespace elements;

use elements\pageMenu\IEntry;

class PageMenu implements IElement
{
    private array $entries = array();

    function addEntry(IEntry $entry) {
        $this->entries[] = $entry;
    }

    function setCurrent(string $current) {
        if ($current != '') {
            foreach ($this->entries as $entry) {
                if ($entry->getName() == $current) {
                    $entry->setCurrent();
                }
            }
        }
    }

    function build(): string
    {
        $str = "";
        foreach ($this->entries as $entry) {
            $str .= $entry->toStr();
        }
        return <<<END
<ul class="navbar">
    $str
</ul>
END;

    }

}




/* //TODO
class DropDownEntry implements IEntry {

}*/