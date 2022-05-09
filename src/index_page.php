<?php

class index_page
{
    public static function getIndex(): \elements\IElement {

        $l = new \elements\SimpleList();

        $l->addElement(new \elements\LiteralElement("<h1>This is the main page!</h1>"));

        return new \elements\bootstrap\Container($l, 'sm d-flex justify-content-center');
    }
}