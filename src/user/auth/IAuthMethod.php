<?php

namespace user\auth;

use elements\IElement;
use JetBrains\PhpStorm\ArrayShape;

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
     * @return array the user data:
     * userid => unique user id, used to connect account
     * username => the current name of the user
     * displayname => the current display name of the user, always specify, even if the same as username
     * email => the email address of the user, please specify
     */
    #[ArrayShape(['id' => "int", 'name' => "string", 'displayname' => "string", 'email' => "string"])]
    function getVerifiedUserData(): array;

    /**
     * @return string The DB name of the method
     */
    function getName(): string;
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