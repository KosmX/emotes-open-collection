<?php

use elements\bootstrap\Container;
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
<small>The animations are working on every minecraft version with the latest mod.</small>
<hr>
<br><br>
<h3>Emotecraft mod</h3>
<h5 style="margin-bottom: 0"><a href="https://emotecraft.kosmx.dev/">Emotecraft Wiki</a> </h5>
<small>wiki made by <a href="https://github.com/Kale-Ko">KaleKo</a></small>
<br>
<br>
<h5><b>Download the mod:</b></h5>
<ul>
<li>CurseForge:
    <ul>
    <li><a href="https://www.curseforge.com/minecraft/mc-mods/emotecraft">for Fabric</a></li>
    <li><a href="https://www.curseforge.com/minecraft/mc-mods/emotecraft-forge/">for Forge</a></li>
    </ul>
</li>
<li><a href="https://modrinth.com/mod/emotecraft">Modrinth</a></>
<li><a href="https://github.com/KosmX/emotes/releases">GitHub</a></>
</ul>
<br><br>
<hr>
This project is Open Source!<br>
<a href="https://github.com/KosmX/emotes-open-collection"><i class="bi bi-github"></i></a>
<hr>
Running an ad-free web service is not free, if you like my work, feel free to donate me!
<br>
<div class="container">
    <div class="row row-cols-auto">
        <div class="col">
        <a href="https://www.patreon.com/bePatron?u=55351945" data-patreon-widget-type="become-patron-button">Become a Patron!</a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
        </div>
        <div class="col">
        <script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Support Me on Ko-fi', '#3ab372', 'G2G6505Y4');kofiwidget2.draw();</script>
        </div>
        <div class="col">
        If possible, use Ko-Fi, it takes less fee.
        </div>
    </div>
</div>

</div>
END));

        return new Container($l, 'sm d-flex justify-content-center');
    }
}