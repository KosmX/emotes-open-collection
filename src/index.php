<?php declare(strict_types=1);

use elements\PageElement;
use elements\LiteralElement;

session_start();
include 'Autoloader.php';

$page = new PageElement();
$page->addElement(new LiteralElement("Hello page builder"));

echo $page->build();

