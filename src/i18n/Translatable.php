<?php declare(strict_types=1);

namespace i18n;

use elements\IElement;

class Translatable implements IElement
{
    private string $key;

    private array $vars;
    private static ?array $strings = null;
    private static ?array $translations = null;

    /**
     * @param string $translationKey
     * @param array $vars variables in PHP style
     */
    public function __construct(string $translationKey, array $vars = array())
    {
        $this->key = $translationKey;
        $this->vars = array();
        foreach ($vars as $key => $val) {
            $this->vars["{\$$key}"] = $val;
        }
    }

    function build(): string
    {
        if (array_key_exists($this->key, self::getStrings())) {
            $str = self::getStrings()[$this->key];
            //var_dump($str);
        } else {
            $str = $this->key;
        }
        return strtr($str, $this->vars);
    }

    public static function getTranslated(string $key, array $vars = array()): string {
        $translatable = new Translatable($key, $vars);
        return $translatable->build();
    }


    private static function getStrings(): array {
        if (Translatable::$strings == null) {
            $translation = self::getLanguage();
            $file = file_get_contents("i18n/lang/en_US.json");
            Translatable::$strings = json_decode($file, true);
            if ($translation != 'en_US') { //To fill empty :D
                $file = file_get_contents("i18n/lang/$translation.json");
                Translatable::$strings = array_merge(Translatable::$strings, json_decode($file, true));
            }
            //var_dump(Translatable::$strings);
        }
        return Translatable::$strings;
    }

    public static function getLanguage(): string {
        if (isset($_COOKIE["language"])) {
            $lang = $_COOKIE['language'];
            if (array_key_exists(str_replace('_', '-', $lang), self::getTranslations())) {
                return $lang;
            }
        }
        return "en_US";
    }

    public static function getTranslations(): array {
        if (Translatable::$translations == null) {
            $file = file_get_contents('i18n/locales.json');
            Translatable::$translations = json_decode($file, true);
        }
        return Translatable::$translations;
    }
}