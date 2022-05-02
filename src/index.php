<?php declare(strict_types=1);
session_name('EOCSession');
session_start();

include 'core.php';
include 'Autoloader.php';
include '404.php';
include 'favicon.php';
include 'pageUtils/pageTemplateUtils.php';

use elements\IElement;
use elements\PageElement;
use elements\LiteralElement;
use routing\Router;
use routing\Routes;


$current = getCurrentPage();

if (!isset($_COOKIE['theme']) || !($_COOKIE['theme'] === 'default')) {
    setcookie('theme', 'default');//, domain: '.kosmx.dev');

}

$R = new Router();

$R->all('~^\\/core(\\.php)?$~')->action(function () {echo <<<END
status-code: 303
location: https://kosmx.dev
END;
});

$R->get('~^\\/favicon\\.ico$~')->action(function () {
    return \favicon\serve();
});

$result = $R->run(getCurrentPage());


if ($result instanceof IElement) {
    $page = new PageElement();

    $page->addElement(utils\getDefaultHeader($current));

    $page->addElement($result);

    $page->addElement(new LiteralElement("Hello page builder"));

    echo $page->build();
} else if ($result instanceof Routes) {
    switch ($result) {
        case Routes::NOT_FOUND:
            \notFound\print404();
            break;
        case Routes::SELF_SERVED:
            break;
        default:

    }
} else if ($result !== null) {
    echo $result;
}
