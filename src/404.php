<?php declare(strict_types=1);

namespace notFound;

use elements\LiteralElement;
use elements\PageElement;


function print404(int $error = 404): void {

    $errors = array(
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        500 => 'Internal Server Error',
        501 => 'Not Implemented'
    );


    http_response_code($error);

    $site = new PageElement();
    $site->title = "EOC: $errors[$error]!";
    $site->overrideCss = "/assets/404.css";
    $currentPage = substr(getCurrentPage(), 1);

    $h1 = $errors[$error];
    $h1 = str_replace(' ', '&nbsp;&nbsp;&nbsp;', $h1);

    $site->addElement( new LiteralElement(<<<END
<div class="screen">
<table>
    <tr><td>
    <h1><snap class="title">$h1!</snap></h1>
    </td></tr>
    <tr><td>
    <h2>/$currentPage &nbsp;&nbsp;forgot &nbsp;&nbsp;the &nbsp;&nbsp;first &nbsp;&nbsp;rule &nbsp;&nbsp;of &nbsp;&nbsp;Minecraft</h2>
    </td></tr>
    <tr><td>
    <h2>Error: &nbsp;&nbsp;<span class="yellow">$error</span></h2>
    </td></tr>
    <tr><td>
    <br><br><br>
    </td></tr>
    <tr><td>
        <a href="/">
            <div class="container">
            <div class="center">Go &nbsp;&nbsp;Back</div>
            </div>
        </a>
        <a href="#" onclick="window.close()">
            <div class="container">
            <div class="center">Close &nbsp;&nbsp;Tab</div>
            </div>
        </a>
    </td></tr>
</table>
</div>
END));

    echo $site->build();
}