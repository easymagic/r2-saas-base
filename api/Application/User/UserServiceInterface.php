<?php

namespace Application\User;

use Domain\User\UserEntity;

interface UserServiceInterface
{
    public function login(string $email, string $password);
    public function register(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code,
    );
    public function create(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code,
    );
    public function updateProfile(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    );
    public function updateUser(
        int $id,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    );
    public function delete(int $id);
    public function updatePassword(int $id, string $password);
    public function changePassword(int $id, string $old_password, string $new_password, string $confirm_password);
    public function find(int $id);
    public function getWalletBalance(int $id);
    public function logout(int $userId);
    /**
     * @param string $email
     * @email string
     * @return mixed
     */
    public function requestForgotPassword(string $email);
    /**
     * @param string $email
     * @param string $otp
     * @param string $password
     * @param string $confirm_password
     * @return mixed
     */
    public function resetPassword(string $email, string $otp, string $password, string $confirm_password);
    /**
     * @param string $email
     * @param string $otp
     * @return mixed
     */
    public function verifyEmail(string $email, string $otp);
    public function fetch(array $criteria);
    public function count(array $criteria);
    public function migrate();
    public function topUpWallet(int $id, float $amount);
    public function withdrawWallet(int $id, float $amount);

    /**
     * @param int $userId
     * @return UserEntity
     */
    public function refreshToken(int $userId);
    /**
     * @param int $userId
     * @return UserEntity
     */
    public function refreshOtp(int $userId);
}
