<?php declare(strict_types=1);

namespace user;

use elements\IElement;
use elements\LiteralElement;
use elements\SimpleList;
use elements\SubmitConstantButton;
use routing\Routes;

class RegisterUser
{

    private int $step = 0;
    private string $state;

    /*
    public function __serialize(): array
    {
        $a = array();
        $a['step'] = $this->step;
        $a['state'] = $this->state;
        return $a;
    }

    public function __unserialize(array $data): void
    {
        $this->state = $data['state'];
        $this->step= $data['step'];
    }*/

    public function __construct() {
        $this->state = self::randomStr();
    }

    public function continue(): ?object
    {
        return match ($this->step) {
            0 => $this->welcomeNewPeople(),
            1 => $this->step1(),
            default => Routes::NOT_FOUND,
        };
    }

    private function welcomeNewPeople(): IElement
    {
        $element = new SimpleList();
        $element->addElement(new LiteralElement(<<<END
<h1>You are not logged in, please log in!</h1>
<h2>Please select one to log-in!</h2>
END
));
        $ghLogin = new LiteralElement(<<<END
<img alt="GH logo" src="/assets/GitHub-Mark-64px.png">
<img src="/assets/GitHub_Logo.png" alt="GitHub" width="200">
END
);
        $params = array();
        $params['client_id'] = "154d6d89f8cf32b9275e"; //GH OAuth Client ID
        $params['redirect_uri'] = "https://emotes.kosmx.dev/u";
        $params['state'] = $this->state;
        $params['scope'] = "read:user";#, user:email";
        $element->addElement(new SubmitConstantButton($ghLogin, $params, "get", "https://github.com/login/oauth/authorize"));

        return $element;
    }

    private function step1(): IElement
    {
        return new LiteralElement("hehe");
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