<?php

use elements\bootstrap\Container;
use i18n\TranslationInsertable;

class index_page
{
    public static function getIndex(): \elements\IElement {

        $l = new \elements\SimpleList();

        $l->addElement(new TranslationInsertable(<<<END
<div>
<h1><\$index.title></h1>
<hr>
<h3><\$index.subtitle></h3>
<small><\$index.title_note></small>
<hr>
<br><br>
<h3><\$index.emotecraft_mod></h3>
<h5 style="margin-bottom: 0"><a href="https://emotecraft.kosmx.dev/"><\$index.emotecraft_wiki></a> </h5>
<small><\$index.wiki_made_by><a href="https://github.com/Kale-Ko">KaleKo</a></small>
<br>
<br>
<h5><b><\$index.downloads></b></h5>
<ul>
<li>CurseForge
    <ul>
    <li><a href="https://www.curseforge.com/minecraft/mc-mods/emotecraft"><\$index.cf.for_fabric></a></li>
    <li><a href="https://www.curseforge.com/minecraft/mc-mods/emotecraft-forge/"><\$index.cf.for_forge></a></li>
    </ul>
</li>
<li><a href="https://modrinth.com/mod/emotecraft">Modrinth</a></>
<li><a href="https://github.com/KosmX/emotes/releases">GitHub</a></>
</ul>
<br><br>
<hr>
<\$index.open_source><br>
<a href="https://github.com/KosmX/emotes-open-collection"><i class="bi bi-github"> GitHub</i></a>
<hr>
<\$index.ad_free>
<br>
<div class="container">
    <div class="row row-cols-auto">
        <div class="col">
        <a href="https://www.patreon.com/bePatron?u=55351945" data-patreon-widget-type="become-patron-button"><\$index.patreon></a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
        </div>
        <div class="col">
        <script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('<\$index.kofi>', '#3ab372', 'G2G6505Y4');kofiwidget2.draw();</script>
        </div>
        <div class="col">
        <\$index.prefer_kofi>
        </div>
    </div>
</div>

</div>
END));

        return new Container($l, 'sm d-flex justify-content-center');
    }
}