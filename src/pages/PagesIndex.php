<?php

namespace pages;

use elements\bootstrap\Container;
use elements\IElement;
use elements\LiteralElement;
use i18n\TranslationInsertable;
use routing\Router;

class PagesIndex
{
    static function getPage(): object {

        $R = new Router(1);

        $R->get(Router::$EMPTY)->action(function () {return self::index();});

        $R->all('~^logs$~')->action(function () {return self::logsHelp();});

        return $R->run(getCurrentPage());

    }

    private static function index(): IElement
    {
        return new Container(new LiteralElement(<<<END
<h1> Static content </h1>
These are static posts, guides... Nothing special is here.
<br>
<a href="/guide/logs">How to get game logs</a>
END
        ));
    }

    private static function logsHelp(): IElement
    {

        $l = self::logEntry("official", "mc");
        $l .= self::logEntry("curseforge", "cf");
        $l .= self::logEntry("prism", "prism");
        //$l .= "<\$page.logs.prism2>";
        $l .= self::logEntry("at", "at");
        $l .= self::logEntry("gd", "gd");

        return new Container(new TranslationInsertable(<<<END

    <meta property="og:url" content="https://emotes.kosmx.dev/guide/logs" />
    <meta content="logs" property="og:title" />
    <meta content="How to get minecraft logs" property="og:description" />
    <meta content="https://emotes.kosmx.dev/assets/logs/logs.png" property="og:image" />
    <!-- Image by Emotecraft community member ؞ n7💪🏼#0396 -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#191919">

<span style="width: 950px">
<h1><\$page.logs.title></h1>
<br>
<h3><\$page.logs.subtitle></h3>
<br>
<\$page.logs.desc>
$l
<hr>
<footer class="text-muted">
<\$page.logs.footnote>
</footer>
</span>
<script>

const collapseElementList = document.querySelectorAll('.collapse');

let openElement = null;

[...collapseElementList].map(e => e.addEventListener('show.bs.collapse', event => {
    if (openElement != null) {
        openElement.hide();
    }
    openElement = bootstrap.Collapse.getOrCreateInstance(e, {toggle: false});
}));

[...collapseElementList].map(e => e.addEventListener('shown.bs.collapse', event => {
    let openedElement = bootstrap.Collapse.getOrCreateInstance(event.target, {toggle: false});
    if (openedElement !== openElement) {
        openedElement.hide();
    }
}));

</script>
END
), 'sm d-flex justify-content-center');
    }

    private static function logEntry(string $id, string $icon): string {
        return <<<END
<hr>
 <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#$id" aria-expanded="false" aria-controls="$id">
   <\$page.logs.button.$id>
 </button>
<div class="collapse" id="$id">
 <br>
<div class="card card-body" style="max-width: 950px">
    <h3><\$page.logs.title.$id></h3>
    <a href="\assets\logs\logs_{$icon}.png" target="_blank">
    <img src="\assets\logs\logs_{$icon}.png" class="text-center img-fluid img-thumbnail" alt="Official minecraft launcher"></a>
    <br>
    <\$page.logs.desc.$id>
    </div>
</div>
END;

    }
}