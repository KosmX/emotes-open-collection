<?php declare(strict_types=1);
namespace utils;

use elements\bootstrap\Navbar;
use elements\bootstrap\navbar\LiteralEntry;

function getDefaultHeader(string $current = ''): Navbar {
    $menu = new Navbar();

    $menu->addEntry(new LiteralEntry("Emotes", "emotes"));
    $menu->addEntry(new LiteralEntry("Account", "user"));
    $menu->addEntry(new LiteralEntry("About", "about"));

    $menu->setCurrent($current);

    return $menu;
}
