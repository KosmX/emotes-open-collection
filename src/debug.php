<?php declare(strict_types=1);

use elements\IElement;
use emotes\Emote;
use routing\Routes;

/**
 * Used to dump environment variables.
 * Only accessible with Admin account
 * @return Routes|IElement
 */
function debugger(): Routes|IElement
{

    var_dump($_REQUEST);
    var_dump($_GET);
    var_dump($_POST);
    var_dump($_SERVER['HTTP_HOST']);
    var_dump($_SESSION);
    var_dump($_FILES);


    $table = new \admin\Table('emotes');

    if (\routing\Method::POST->isActive()) {
        $table->post();
    }


    return $table;
}
