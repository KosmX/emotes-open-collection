<?php declare(strict_types=1);

namespace notFound;

use elements\LiteralElement;
use elements\PageElement;

function print404(): void {

    http_response_code(404);

    $site = new PageElement();
    $site->title = 'EOC: Page not found!';
    $site->overrideCss = "/assets/404.css";
    $currentPage = substr(getCurrentPage(), 1);

    $site->addElement( new LiteralElement(<<<END
<div class="screen">
<table>
    <tr><td>
    <h1><snap class="title">Not &nbsp;&nbsp;Found!</snap></h1>
    </td></tr>
    <tr><td>
    <h2>$currentPage &nbsp;&nbsp;forgot &nbsp;&nbsp;the &nbsp;&nbsp;first &nbsp;&nbsp;rule &nbsp;&nbsp;of &nbsp;&nbsp;Minecraft</h2>
    </td></tr>
    <tr><td>
    <h2>Error: &nbsp;&nbsp;<span class="yellow">404</span></h2>
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