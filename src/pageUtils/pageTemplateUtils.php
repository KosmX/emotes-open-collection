<?php declare(strict_types=1);
namespace utils;

use elements\PageMenu;
use elements\pageMenu\LiteralEntry;

function getDefaultHeader(string $current = ''): PageMenu {
    $menu = new PageMenu();

    $menu->addEntry(new LiteralEntry("Emotes", "emotes"));
    $menu->addEntry(new LiteralEntry("Account", "account"));
    $menu->addEntry(new LiteralEntry("About", "about"));

    $menu->setCurrent($current);

    return $menu;
}
