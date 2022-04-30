<?php declare(strict_types=1);
session_name('EOCSession');
session_start();

include 'core.php';
include 'Autoloader.php';
include 'pageUtils/pageTemplateUtils.php';

use elements\PageElement;
use elements\LiteralElement;


$current = getCurrentPage();

if (!isset($_COOKIE['theme']) || !($_COOKIE['theme'] === 'default')) {
    setcookie('theme', 'default');//, domain: '.kosmx.dev');

}

$page = new PageElement();

$page->addElement(utils\getDefaultHeader($current));

$page->addElement(new LiteralElement("Hello page builder"));

echo $page->build();

