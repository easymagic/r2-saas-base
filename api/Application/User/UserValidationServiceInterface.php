<?php

namespace Application\User;

use Domain\User\UserEntity;

interface UserValidationServiceInterface
{
    public function validateLogin(string $email, string $password);
    public function validateRegister(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    );
    public function validateCreate(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    );
    public function validateUpdateProfile(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    );
    /**
     * @param int $id
     * @return UserEntity
     */
    public function validateDelete(int $id);
    public function validateUpdateUser(
        int $id,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    );
    public function validateUpdatePassword(int $id, string $password);
    public function validateChangePassword(int $id, string $old_password, string $new_password, string $confirm_password);
    public function validateVerifyEmail(string $email, string $otp);
    public function validateResetPassword(string $email, string $otp, string $password, string $confirm_password);
    public function validateTopUpWallet(int $id, float $amount);
    public function validateWithdrawWallet(int $id, float $amount);
}
