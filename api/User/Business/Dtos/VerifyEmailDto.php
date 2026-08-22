<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class VerifyEmailDto
{
    public string $email;
    public string $otp;

    public function __construct(string $email, string $otp)
    {
        Contracts::requiresNotNullOrEmpty($email, 'Email');
        Contracts::requiresNotNullOrEmpty($otp, 'OTP');

        $this->email = $email;
        $this->otp = $otp;
    }
}
