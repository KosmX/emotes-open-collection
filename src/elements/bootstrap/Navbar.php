<?php declare(strict_types=1);

namespace elements\bootstrap;

use elements\bootstrap\navbar\IEntry;
use elements\IElement;

class Navbar implements IElement
{
    private array $entries = array();

    function addEntry(IEntry $entry) {
        $this->entries[] = $entry;
    }

    function setCurrent(string $current) {
        if ($current != '') {
            foreach ($this->entries as $entry) {
                if (str_starts_with($current, $entry->getName())) {
                    $entry->setCurrent();
                }
            }
        }
    }

    function build(): string
    {
        $str = "";
        foreach ($this->entries as $entry) {
            $str .= $entry->toStr();
        }
        return <<<END
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">
    <img src="/assets/favicon.png" alt="" width="32" height="32" class="d-inline-block align-text-top">
    Emotes Open Collection
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      $str
      </ul>
      <form class="d-flex" method="get" action="/e">
        <input class="form-control me-2" type="search" name="s" placeholder="Search Emote" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
END;

    }

}




/* //TODO
class DropDownEntry implements IEntry {

}*/