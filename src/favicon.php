<?php declare(strict_types=1);

namespace favicon;


use routing\Routes;

function serve(): Routes
{
    header("content-type: image/x-icon");
    readfile('assets/favicon.ico');

    return Routes::SELF_SERVED;
}