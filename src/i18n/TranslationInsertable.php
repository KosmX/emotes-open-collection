<?php declare(strict_types=1);

namespace i18n;

use elements\IElement;

class TranslationInsertable implements IElement
{
    private string $string;

    /**
     * @param $string
     *
     */
    public function __construct($string)
    {
        $matches = [];
        preg_match_all("~<\\$([^<>]+)>~", $string, $matches);

        $keys = array_map(function (string $key) {
            $translated = Translatable::getTranslated($key);
            if ($translated == null) $translated = $key;
            return $translated;
        }, $matches[1]);

        $toReplace = [];
        for ($i = 0; $i < min(count($matches[0]), count($keys)); $i++) {
            $toReplace[$matches[0][$i]] = $keys[$i];
        }

        $this->string = strtr($string, $toReplace);
    }


    function build(): string
    {
        return $this->string;
    }
}