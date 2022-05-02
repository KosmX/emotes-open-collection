<?php declare(strict_types=1);

function debugger(): \routing\Routes
{
    var_dump($_REQUEST);
    var_dump($_GET);
    var_dump($_POST);

    return \routing\Routes::SELF_SERVED;
}
