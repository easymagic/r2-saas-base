<?php

namespace User\Business;

use Shared\AbstractBaseServiceInterface;
use Data\User\UserEntity;

interface UserServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * Login a user
     * @param string $email
     * @param string $password
     * @return UserEntity
     */
    public function login(string $email, string $password);
    
    /**
     * Register a new user
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $phone
     * @param string $delivery_address
     * @param string $social_security_number
     * @param string $role
     * @param string $status
     * @param string $country_code
     * @return UserEntity
     */
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

    /**
     * Create a new user
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $phone
     * @param string $delivery_address
     * @param string $social_security_number
     * @param string $role
     * @param string $status
     * @param string $country_code
     * @return UserEntity
     */
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

    /**
     * Update a user's profile
     * @param int $id
     * @param string $name
     * @param string $phone
     * @param string $delivery_address
     * @return UserEntity
     */
    public function updateProfile(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    );

    /**
     * Update a user
     * @param int $id
     * @param string $name
     * @param string $phone
     * @param string $delivery_address
     * @param string $social_security_number
     * @param string $role
     * @param string $status
     * @param string $country_code
     * @return UserEntity
     */
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

    /**
     * Delete a user
     * @param int $id
     * @return bool
     */
    public function delete(int $id);

    /**
     * Update a user's password
     * @param int $id
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $id, string $password);

    /**
     * Change a user's password
     * @param int $id
     * @param string $old_password
     * @param string $new_password
     * @param string $confirm_password
     * @return bool
     */
    public function changePassword(int $id, string $old_password, string $new_password, string $confirm_password);

    /**
     * Find a user by id
     * @param int $id
     * @return UserEntity
     */
    public function find(int $id);

    /**
     * Get a user's wallet balance
     * @param int $id
     * @return float
     */
    public function getWalletBalance(int $id);

    /**
     * Logout a user
     * @param int $userId
     * @return bool
     */
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
