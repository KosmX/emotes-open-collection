<?php declare(strict_types=1);

namespace elements;

class PageElement implements IElement
{
    use ListTrait;

    public string $title = "Emotes Open Collection";
    public ?string $overrideCss = null;

    function build(): string
    {
        $str = "";
        foreach ($this->elements as $element) {
            $str .= $element->build();
        }
        if ($this->overrideCss === null) {
            $css = '/assets/' . $_COOKIE['theme'] . '.css';
        } else {
            $css = $this->overrideCss;
        }

        return <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>$this->title</title>
    <meta http-equiv="cache-control" content="no-cache, must-revalidate">
    <link rel="stylesheet", href="$css">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
$str
</body>
</html>
END;

    }
}