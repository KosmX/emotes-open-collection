<?php declare(strict_types=1);
session_name('EOCSession');
session_start();

include 'core.php';
include 'Autoloader.php';
include 'pageUtils/pageTemplateUtils.php';

use elements\IElement;
use elements\PageElement;
use elements\LiteralElement;
use routing\Router;


$current = getCurrentPage();

if (!isset($_COOKIE['theme']) || !($_COOKIE['theme'] === 'default')) {
    setcookie('theme', 'default');//, domain: '.kosmx.dev');

}

$R = new Router();

$R->all('~^\\/core(\\/.php)?$~')->action(function () {echo <<<END
status-code: 303
location: https://kosmx.dev
END;
});

$result = $R->run(getCurrentPage());


if ($result instanceof IElement) {
    $page = new PageElement();

    $page->addElement(utils\getDefaultHeader($current));

    $page->addElement($result);

    $page->addElement(new LiteralElement("Hello page builder"));

    echo $page->build();
} else if ($result !== null) {
    echo $result;
} else {
    http_response_code(404);
    echo <<<END
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>EOC: Page not found!</title>
        <link rel="stylesheet", href="/assets/404.css">
        <meta http-equiv="cache-control" content="no-cache, must-revalidate">
    </head>
    <body>
        <h1>Page not found</h1>
    </body>
</html>
END;

}

