<?php declare(strict_types=1);

use elements\bootstrap\Container;
use elements\IElement;
use elements\LiteralElement;
use i18n\Translatable;
use routing\Routes;

class Cookies
{
    public static function getPageAtTarget(bool $alwaysRedirect = false): IElement|Routes
    {
        $accept_cookies = Translatable::getTranslated("accept_cookies");
        $deny_cookies = Translatable::getTranslated("deny_cookies");
        $text = Translatable::getTranslated("cookies", array("privacy"=>"<a href=/privacy>privacy</a>"));
        $redirectBool = $alwaysRedirect ? 'true' : 'false';

        return new Container(new LiteralElement(<<<END
$text
<br>
<button type="button" class="btn btn-success" onclick="accept()">$accept_cookies</button>
<button type="button" class="btn btn-secondary" onclick="deny()">$deny_cookies</button>
<script>
function deleteAllCookies() {
    const cookies = document.cookie.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }
}
function accept() {
    document.cookie = "enable_cookies=true";
    if ($redirectBool) { //inserted bool from PHP
        window.location.href = "/";
    } else {
        window.location.reload();
    }
}
function deny() {
    deleteAllCookies()
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