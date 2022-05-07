<?php

namespace user\auth;

class IllegalStateException extends \Error
{
    private string $authService;

    /**
     * @param string $authService
     */
    public function __construct(string $authService)
    {
        parent::__construct("Auth service: $authService has invalid state");
    }
}