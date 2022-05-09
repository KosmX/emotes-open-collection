<?php declare(strict_types=1);
namespace utils;

use elements\bootstrap\Navbar;
use elements\bootstrap\navbar\DropdownEntry;
use elements\bootstrap\navbar\LiteralEntry;
use pageUtils\UserHelper;

function getDefaultHeader(string $current = ''): Navbar {
    $menu = new Navbar();

    $menu->addEntry(new LiteralEntry("Emotes", "emotes"));
    if (UserHelper::getCurrentUser() != null) {
        $userEntry = new DropdownEntry(UserHelper::getCurrentUser()->displayName, 'user');

        $userEntry->addEntry('/u', 'User page');
        $userEntry->addEntry('/settings/profile', 'Profile settings');
        $userEntry->addSeparator();
        $userEntry->addEntry('/logout', 'Log out');

        $menu->addEntry($userEntry);
    } else {
        $menu->addEntry(new LiteralEntry("Account", "user"));
    }

    $menu->setCurrent($current);

    return $menu;
}
