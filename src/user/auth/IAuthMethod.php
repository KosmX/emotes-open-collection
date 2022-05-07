<?php

namespace user\auth;

use elements\IElement;

interface IAuthMethod
{
    function getAuthButton(): IElement;

    /**
     * @return bool|IElement true if success, false if failed auth (just someone typed the URL), IElement if error containing the error text
     * @throws IllegalStateException if auth state does not match
     */
    function authCallback(): mixed;

    /**
     * If auth was success, we can have the corresponding user ID! Let's get it from GH
     * @return int
     */
    function getVerifiedUserID(): int;
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