<?php

use elements\LiteralElement;

class index_page
{
    public static function getIndex(): \elements\IElement {

        $l = new \elements\SimpleList();

        $l->addElement(new LiteralElement(<<<END
<div>
<h1>Welcome to Emotes Open Collection!</h1>
<hr>
<h3>Use the navbar to search, or log-in to upload your own emotes!</h3>
</div>
END));

        return new \elements\bootstrap\Container($l, 'sm d-flex justify-content-center');
    }
}