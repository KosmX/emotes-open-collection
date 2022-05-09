<?php declare(strict_types=1);
session_name('EOCSession');
session_start();

include 'core.php';
include 'Autoloader.php';
include '404.php';
include 'debug.php';
include 'favicon.php';
include 'pageUtils/pageTemplateUtils.php';

use elements\IElement;
use elements\LiteralElement;
use elements\PageElement;
use routing\Router;
use routing\Routes;

$current = '';


$R = new Router();


$R->get('~^\\/favicon\\.ico$~')->action(function () {
    return \favicon\serve();
});

$R->all('~^\\/u(ser)?(\\/|$)~')->action(function () use (&$current) {$current = 'user'; return \user\AccountPage::getPage();});

$R->all('~^\\/debug(\\.php)?$~')->action(function () {return debugger();});
$R->get('~^$~')->action(function () {return index_page::getIndex();});



// --- RESULT PROCESSING
$result = $R->run(getCurrentPage());



if ($result instanceof IElement) {
    $page = new PageElement();
    $page->enableBootstrap = true;

    $page->addElement(utils\getDefaultHeader($current));

    $page->addElement(\elements\bootstrap\Container::getDefaultSpacer($result));

    //$page->addElement(new LiteralElement("Hello page builder"));

    echo $page->build();
} else if ($result instanceof Routes) {
    switch ($result) {
        case Routes::NOT_FOUND:
            \notFound\print404();
            break;
        case Routes::SELF_SERVED:
            break;
        default:
            echo "unimplemented route: $result";
    }
} else if ($result !== null) {
    echo $result;
} else {
    \notFound\print404();
}