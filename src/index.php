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
use elements\PageElement;
use routing\Router;
use routing\Routes;
use user\AccountPage;
use function notFound\print404;

$current = '';


$R = new Router();


$R->get('~^\\/favicon\\.ico$~')->action(function () {
    return \favicon\serve();
});

$R->all('~^\\/register(\\/|$)~')->action(function () use (&$current) {$current = 'user'; return AccountPage::getPage();});
$R->all('~^\\/u(ser)?\\/~')->action(function () use (&$current) {
    $current = 'user';
    return AccountPage::getAccountPage();
});
$R->all('~^\\/u(ser)?$~')->action(function () use (&$current) {
    $current = 'user';
    if (\pageUtils\UserHelper::getCurrentUser() != null) return AccountPage::getAccountPage();
    else return AccountPage::getPage();
});

$R->all('~^\\/settings\\/(editP|p)rofile(\\/|$)~')->action(function () use (&$current) {$current = 'user'; return AccountPage::userSettings();});
$R->all('~^\\/settings\\/(editP|p)rofile(\\/|$)~')->action(function () use (&$current) {$current = 'user'; return AccountPage::userSettings();});
$R->all('~^\\/settings\\/delete$~')->action(function () use (&$current) {$current = 'user'; return AccountPage::deleteUser();});

$R->get('~^\\/logout$~')->action(function () {return AccountPage::logout();});

$R->all('~^\\/debug(\\.php)?$~')->action(function () {return debugger();});
$R->get('~^$~')->action(function () {return index_page::getIndex();});

$R->all('~^\\/e(motes)?(\\/|$)~')->action(function () use (&$current) {$current = 'emotes'; return \emotes\index::index();});



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
            print404();
            break;
        case Routes::SELF_SERVED:
            break;
        case Routes::AUTH_REQUIRED:
            print404(401);
            break;
        case Routes::INTERNAL_ERROR:
            print404(500);
            break;
        default:
            echo "unimplemented route: $result";
    }
} else if ($result !== null) {
    echo $result;
} else {
    print404();
}