<?php

namespace User\Business;

use Shared\AbstractBaseServiceInterface;
use User\Data\UserEntity;
use Shared\Query\QueryObject;
use User\Business\Dtos\ChangePasswordDto;
use User\Business\Dtos\CreateDto;
use User\Business\Dtos\LoginDto;
use User\Business\Dtos\RegisterDto;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Dtos\ResetPasswordDto;
use User\Business\Dtos\TopUpWalletDto;
use User\Business\Dtos\UpdatePasswordDto;
use User\Business\Dtos\UpdateProfileDto;
use User\Business\Dtos\UpdateUserDto;
use User\Business\Dtos\VerifyEmailDto;
use User\Business\Dtos\WithdrawWalletDto;

interface UserServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * Login a user
     * @param LoginDto $loginDto
     * @return UserEntity
     */
    public function login(LoginDto $loginDto);

    /**
     * Register a new user
     * @param RegisterDto $registerDto
     * @return UserEntity
     */
    public function register(RegisterDto $registerDto);

    /**
     * Create a new user
     * @param CreateDto $createDto
     * @return UserEntity
     */
    public function create(CreateDto $createDto);

    /**
     * Update a user's profile
     * @param UpdateProfileDto $updateProfileDto
     * @return UserEntity
     */
    public function updateProfile(UpdateProfileDto $updateProfileDto);

    /**
     * Update a user
     * @param UpdateUserDto $updateUserDto
     * @return UserEntity
     */
    public function updateUser(UpdateUserDto $updateUserDto);

    /**
     * Delete a user
     * @param int $id
     * @return bool
     */
    public function delete(int $id);

    /**
     * Update a user's password
     * @param UpdatePasswordDto $updatePasswordDto
     * @return bool
     */
    public function updatePassword(UpdatePasswordDto $updatePasswordDto);

    /**
     * Change a user's password
     * @param ChangePasswordDto $changePasswordDto
     * @return bool
     */
    public function changePassword(ChangePasswordDto $changePasswordDto);

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
     * @param RequestForgotPasswordDto $requestForgotPasswordDto
     * @return mixed
     */
    public function requestForgotPassword(RequestForgotPasswordDto $requestForgotPasswordDto);

    /**
     * @param ResetPasswordDto $resetPasswordDto
     * @return mixed
     */
    public function resetPassword(ResetPasswordDto $resetPasswordDto);

    /**
     * @param VerifyEmailDto $verifyEmailDto
     * @return mixed
     */
    public function verifyEmail(VerifyEmailDto $verifyEmailDto);

    public function migrate();

    /**
     * @param TopUpWalletDto $topUpWalletDto
     * @return UserEntity
     */
    public function topUpWallet(TopUpWalletDto $topUpWalletDto);

    /**
     * @param WithdrawWalletDto $withdrawWalletDto
     * @return UserEntity
     */
    public function withdrawWallet(WithdrawWalletDto $withdrawWalletDto);

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

    /**
     * @param array $filters
     * @return QueryObject
     */
    public function fetchUsersAsAdmin(array $filters = []);
}
