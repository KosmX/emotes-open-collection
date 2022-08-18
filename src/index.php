<?php declare(strict_types=1);
if (isset($_COOKIE['enable_cookies'])) {
    session_name('EOCSession');
    session_start();
}

include 'core.php';
include 'Autoloader.php';
include '404.php';
include 'debug.php';
include 'favicon.php';
include 'pageUtils/pageTemplateUtils.php';

use elements\IElement;
use elements\PageElement;
use pageUtils\UserHelper;
use routing\Router;
use routing\Routes;
use user\AccountPage;
use function notFound\print404;

$current = '';

// -- Some sort of vulnerability mitigation -- //

if (str_starts_with($_SERVER['REQUEST_URI'], '//')) {
    print404();
    exit(0);
}

// -- end section -- //

if (getCurrentPage() != '' && str_ends_with(parse_URL($_SERVER['REQUEST_URI'])['path'], '/')) {
    redirect(getCurrentPage());
    exit(0);
}

if (UserHelper::getCurrentUser() == null || UserHelper::getCurrentUser()->privileges < 2) {
    error_reporting(0);
}

$R = new Router();


$R->get('~^\\/favicon\\.ico$~')->action(function () {
    return \favicon\serve();
});
$R->get('~^\\/robots(\\.txt)?$~')->action(function () {return \favicon\serverRobots();});

$R->get('~^\\/terms$~')->action(function () {return new \elements\LiteralElement(file_get_contents('terms.html'));});
$R->get('~^\\/privacy$~')->action(function () {return new \elements\LiteralElement(file_get_contents('privacy.html'));});

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
$R->all('~^\\/themes$~')->action(function () use (&$current) {$current = 'user'; return AccountPage::themes();});

$R->get('~^\\/logout$~')->action(function () {return AccountPage::logout();});

$R->all('~^\\/debug(\\.php)?$~')->action(function () {
    if (UserHelper::getCurrentUser() != null && UserHelper::getCurrentUser()->privileges >= 8) {
        return debugger();
    } else return Routes::NOT_FOUND;
});
$R->get('~^$~')->action(function () {return index_page::getIndex();});

$R->all('~^\\/e(motes)?(\\/|$)~')->action(function () use (&$current) {$current = 'emotes'; return \emotes\index::index();});

$R->get('~^\\/lang$~')->action(function () {return \i18n\LanguageSelector::getLanguageScreen();});

$R->all('~^\\/admin(\\/|$)~')->action(function () {return \admin\index::index();});



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
        case Routes::FORBIDDEN:
            print404(403);
            break;
        default:
            echo "unimplemented route: $result";
    }
} else if ($result !== null) {
    echo $result;
} else {
    print404();
}