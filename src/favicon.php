<?php declare(strict_types=1);

namespace favicon;


use routing\Routes;

function serve(): Routes
{
    header("content-type: image/x-icon");
    readfile('assets/favicon.ico');

    return Routes::SELF_SERVED;
}

function serverRobots(): Routes
{
    header('content-type: text/plain');
    echo <<<ROBOTS
User-agent: *
Disallow: /settings/

ROBOTS;

    return Routes::SELF_SERVED;
}