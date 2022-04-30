<?php declare(strict_types=1);

namespace elements;

class PageElement implements IElement
{
    private array $elements = array();

    public string $title = "Emotes Open Collection";

    function addElement(IElement $element) {
        $this->elements[] = $element;
    }

    function build(): string
    {
        $str = "";
        foreach ($this->elements as $element) {
            $str .= $element->build();
        }
        $css = '/assets/'.$_COOKIE['theme'].'.css';
        return <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>$this->title</title>
    <meta http-equiv="cache-control" content="no-cache, must-revalidate">
    <link rel="stylesheet", href="$css">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png" />
</head>
<body>
$str
</body>
</html>
END;

    }
}