<?php declare(strict_types=1);
namespace utils;

use elements\bootstrap\Navbar;
use elements\bootstrap\navbar\DropdownEntry;
use elements\bootstrap\navbar\LiteralEntry;
use pageUtils\UserHelper;

function getDefaultHeader(string $current = ''): Navbar {
    $menu = new Navbar();

    if (UserHelper::getCurrentUser() != null) {
        $emotesEntry = new DropdownEntry('Emotes', 'emotes');

        $emotesEntry->addEntry('/e', 'Emotes');
        $emotesEntry->addEntry('/e/starred', '<i class="bi bi-star-fill"></i> Starred emotes');
        $emotesEntry->addSeparator();
        $emotesEntry->addEntry('/e/new', '<i class="bi bi-upload"></i> Upload emote');
        $emotesEntry->addEntry('/e/my', 'My emotes');
        $emotesEntry->addEntry('/e/tmp', 'Unpublished emotes');

        $menu->addEntry($emotesEntry);


        $userEntry = new DropdownEntry(UserHelper::getCurrentUser()->displayName, 'user');

        $userEntry->addEntry('/u', 'User page');
        $userEntry->addEntry('/settings/profile', 'Profile settings');
        $userEntry->addSeparator();
        $userEntry->addEntry('/logout', 'Log out');

        $menu->addEntry($userEntry);
    } else {
        $menu->addEntry(new LiteralEntry("Emotes", "emotes"));
        $menu->addEntry(new LiteralEntry("Account", "user"));
    }

    $menu->setCurrent($current);

    return $menu;
}
