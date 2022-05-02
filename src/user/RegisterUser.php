<?php declare(strict_types=1);

namespace user;

use elements\IElement;

class RegisterUser
{

    private int $step = 0;
    private string $state;

    public function __construct() {
        $this->state = self::randomStr();
    }

    public function continue(): IElement
    {

        switch ($this->step) {
            case 0:
                return $this->welcomeNewPeople();
                break;
            case 1:
                return $this->step1();
        }

    }

    private function welcomeNewPeople(): IElement
    {
        $element =
    }

    private function step1(): IElement
    {

    }

    private static function randomStr(int $length = 16): string {
        //From stackoverflow https://stackoverflow.com/questions/4356289/php-random-string-generator
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}