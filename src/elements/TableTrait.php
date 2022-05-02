<?php declare(strict_types=1);

namespace elements;

trait TableTrait
{
    private array $rows = array();

    public function addRow(IElement $element): void
    {
        $this->rows[] = $element;
    }

    protected function buildTable(?string $tag = null): string
    {
        $rows = '';
        foreach ($this->rows as $row) {
            $rows .= "<tr>$row</tr>";
        }
        if ($tag === null) {
            $class = '';
        } else {
            $class = " class=\"$tag\"";
        }
        return "<table$class>$rows</table>";
    }
}