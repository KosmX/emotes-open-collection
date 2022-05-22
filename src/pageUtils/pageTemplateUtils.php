<?php declare(strict_types=1);
namespace utils;

use elements\bootstrap\Navbar;
use elements\bootstrap\navbar\DropdownEntry;
use elements\bootstrap\navbar\LiteralEntry;
use elements\IElement;
use elements\LiteralElement;
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
        $userEntry->addEntry('/themes', 'Themes');
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


function getPageButtons(int $length, int $currentPage, int $pageSize = 20): IElement
{
    $length = max($length, 1);
    $maxPage = ceil($length/$pageSize);
    $currentPage = min($maxPage - 1, $currentPage); //before something bad happens
    $before = min(2, $currentPage);
    $after = min(2, $maxPage - $currentPage - 1);

    $pages = '';

    $currentUrl = getCurrentPage().'?';
    if (isset($_GET['from'])) {
        $currentUrl .= "from={$_GET['from']}&";
    }
    if (isset($_GET['s'])) {
        $currentUrl .= "s={$_GET['s']}&";
    }

    // Start generating buttons

    if ($currentPage > 2) {
        $pages .= <<<END
    <li class="page-item">
      <a href="{$currentUrl}p=0" class="page-link">&lt;&lt;&lt;</a>
    </li>
END;
    }

    if ($currentPage > 3) {
        $pages .= <<<END
    <li class="page-item disabled">
      <span class="page-link">...</span>
    </li>
END;
    }

    for ($i = 0; $i < $before; $i++) {
        $index = $currentPage - $before + $i;
        $iPlus = $index + 1;
        $pages .= <<<END
    <li class="page-item">
      <a href="{$currentUrl}p=$index" class="page-link">$iPlus</a>
    </li>
END;
    }

    $iPlus = $currentPage + 1;
    $pages .= <<<END
    <li class="page-item active">
      <span class="page-link">
        $iPlus
      </span>
    </li>
END;

    for ($i = 1; $i <= $after; $i++) {
        $index = $currentPage + $i;
        $iPlus = $index + 1;
        $pages .= <<<END
    <li class="page-item">
      <a href="{$currentUrl}p=$index" class="page-link">$iPlus</a>
    </li>
END;
    }

    if ($maxPage - $currentPage > 4) {
        $pages .= <<<END
    <li class="page-item disabled">
      <span class="page-link">...</span>
    </li>
END;
    }

    if ($maxPage - $currentPage > 3) {
        $mp = $maxPage - 1;
        $pages .= <<<END
    <li class="page-item">
      <a href="{$currentUrl}p=$mp" class="page-link">&gt;&gt;&gt;</a>
    </li>
END;
    }


    return new LiteralElement(<<<END
<nav aria-label="Page navigation example">
  <ul class="pagination justify-content-center">
    $pages
  </ul>
</nav>
END
    );
}
