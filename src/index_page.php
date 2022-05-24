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
<h5><a href="https://emotecraft.kosmx.dev/>">Emotecraft Wiki</a> </h5>
<small>by <a href="https://github.com/Kale-Ko">KaleKo</a></small>
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
</div>
END));

        return new Container($l, 'sm d-flex justify-content-center');
    }
}