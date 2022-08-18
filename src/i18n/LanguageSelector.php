<?php declare(strict_types=1);

namespace i18n;

use elements\bootstrap\Container;
use elements\IElement;
use elements\LiteralElement;
use routing\Routes;

class LanguageSelector
{
    public static function getLanguageScreen(): IElement|Routes {
        $cookieTest = \Cookies::testCookies();
        if ($cookieTest != null) return $cookieTest;

        return self::generateMenu();
    }

    private static function generateMenu(): IElement
    {

        $options = "";
        $len = 0;
        foreach (Translatable::getTranslations() as $key => $lang) {
            $key = str_replace('-', '_', $key);
            $selected = "";
            if ($key == Translatable::getLanguage()) {
                $selected = " selected";
            }
            $str = join(' - ', array_reverse($lang));
            $options .= "<option$selected value='$key'>$str</option>\n";
            $len++;
        }

        return Container::getDefaultSpacer(new LiteralElement(<<<END
<script>
function formSubmission() {
    const form = document.selector.list;
    
    const selectedValues = [].filter
       .call(form.options, option => option.selected)
       .map(option => option.value)[0];
    document.cookie = "language=" + selectedValues;
    document.location.href = '/'
}
</script>
<div>
<span class="">
<button type="button" class="btn btn-primary text-center" onclick="formSubmission()">Confirm</button>
</span>
<hr>
<form id="selector" name="selector">
<select id="list" name="list" class="form-select text-center" size="$len" style='overflow:hidden' aria-label="size 3 select example">
$options
</select>
</form>
</div>
END
));
    }
}