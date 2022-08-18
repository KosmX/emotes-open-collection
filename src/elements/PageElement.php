<?php declare(strict_types=1);

namespace elements;

use pageUtils\UserHelper;

class PageElement implements IElement
{
    static ?string $meta = null;

    use ListTrait;

    public string $title = "Emotes Open Collection";
    public ?string $overrideCss = null;
    public bool $enableBootstrap = false;

    function build(): string
    {
        $str = "";
        foreach ($this->elements as $element) {
            $str .= $element->build();
        }
        if ($this->enableBootstrap) {
            //$str .= '<script src="/bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>';
            $str .= '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>';
        }


        if ($this->overrideCss === null) {
            $css = UserHelper::getTheme();
        } else {
            $css = $this->overrideCss;
        }

        $meta = self::$meta ?? '';

        return <<<END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>$this->title</title>
    <meta http-equiv="cache-control" content="no-cache, must-revalidate">
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    $meta
    
    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="$css">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon.png" />
    
    <!-- Open Search XML -->
    <link rel="search" type="application/opensearchdescription+xml" href="/assets/opensearch.xml" title="Emotes">
</head>
<body>
$str
</body>
</html>
END;

    }
}