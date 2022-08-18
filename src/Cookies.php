<?php declare(strict_types=1);

use elements\bootstrap\Container;
use elements\IElement;
use elements\LiteralElement;
use i18n\Translatable;
use routing\Routes;

class Cookies
{
    public static function getPageAtTarget(): IElement|Routes
    {
        $accept_cookies = Translatable::getTranslated("accept_cookies");
        $deny_cookies = Translatable::getTranslated("deny_cookies");
        $text = Translatable::getTranslated("cookies", array("privacy"=>"<a href=/privacy>privacy</a>"));

        return new Container(new LiteralElement(<<<END
$text
<br>
<button type="button" class="btn btn-success" onclick="accept()">$accept_cookies</button>
<button type="button" class="btn btn-secondary" onclick="deny()">$deny_cookies</button>
<script>
function accept() {
    document.cookie = "enable_cookies=true"
    window.location.reload();
}
function deny() {
    window.location.href = "/"
}
</script>
END
));
    }

    /**
     * Test if cookies are enabled
     * @return IElement|null null if cookies are good, IElement cookie enablement form
     */
    public static function testCookies(): ?IElement {
        if (isset($_COOKIE['enable_cookies'])) {
            return null;
        }
        return self::getPageAtTarget();
    }
}