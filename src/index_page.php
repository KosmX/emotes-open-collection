<?php declare(strict_types=1);

use elements\bootstrap\Container;
use elements\LiteralElement;

class index_page
{
    public static function getIndex(): \elements\IElement {

        $l = new \elements\SimpleList();

        $content = new \i18n\Translatable("index", array("support"=><<<END
        <div class="col">
        <a href="https://www.patreon.com/bePatron?u=55351945" data-patreon-widget-type="become-patron-button">Become a Patron!</a><script async src="https://c6.patreon.com/becomePatronButton.bundle.js"></script>
        </div>
        <div class="col">
        <script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Support Me on Ko-fi', '#3ab372', 'G2G6505Y4');kofiwidget2.draw();</script>
        </div>
        END));
        $content = $content->build();
        $l->addElement(new LiteralElement(<<<END
<div>
$content
</div>
END));

        return new Container($l, 'sm d-flex justify-content-center');
    }
}