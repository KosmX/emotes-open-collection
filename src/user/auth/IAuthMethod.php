<?php

namespace user\auth;

use elements\IElement;

interface IAuthMethod
{
    function getAuthButton(): IElement;

    function authCallback(): bool;
}


function randomStr(int $length = 16): string {
    //From stackoverflow https://stackoverflow.com/questions/4356289/php-random-string-generator
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}