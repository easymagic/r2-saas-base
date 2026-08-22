<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class RequestForgotPasswordDto
{
    public string $email;

    public function __construct(string $email)
    {
        Contracts::requiresNotNullOrEmpty($email, 'Email');
        Contracts::requires(filter_var($email, FILTER_VALIDATE_EMAIL) !== false, 'Email must be a valid email address');

        $this->email = $email;
    }
}
