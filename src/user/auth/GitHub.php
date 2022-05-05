<?php

namespace user\auth;

use elements\IElement;

class GitHub implements IAuthMethod
{
    private string $state;

    function getAuthButton(): IElement
    {
        // TODO: Implement getAuthButton() method.
    }
}