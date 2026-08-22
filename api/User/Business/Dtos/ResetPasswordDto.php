<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class ResetPasswordDto
{
    public string $email;
    public string $otp;
    public string $password;
    public string $confirm_password;

    public function __construct(
        string $email,
        string $otp,
        string $password,
        string $confirm_password
    ) {
        Contracts::requiresNotNullOrEmpty($email, 'Email');
        Contracts::requiresNotNullOrEmpty($otp, 'OTP');
        Contracts::requiresNotNullOrEmpty($password, 'Password');
        Contracts::requiresNotNullOrEmpty($confirm_password, 'Confirm Password');
        Contracts::requires($password === $confirm_password, 'Password and confirm password do not match');

        $this->email = $email;
        $this->otp = $otp;
        $this->password = $password;
        $this->confirm_password = $confirm_password;
    }
}
