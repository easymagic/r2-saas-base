<?php

namespace User\Business;

use User\Data\UserRepositoryInterface;
use User\Data\UserEntity;
use Exception;

class UserValidationService implements UserValidationServiceInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }


    public function validateLogin(string $email, string $password)
    {
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (empty($password)) {
            throw new Exception('Password is required');
        }
        return true;
    }

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
    ) {
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (empty($password)) {
            throw new Exception('Password is required');
        }
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($phone)) {
            throw new Exception('Phone is required');
        }
        if (empty($delivery_address)) {
            throw new Exception('Delivery address is required');
        }

        if (empty($role)) {
            // throw new Exception('Role is required');
        }

        // role agent 
        if ($role == 'agent') {
            if (empty($social_security_number)) {
                throw new Exception('Social security number is required');
            }
        }

        if (empty($status)) {
            throw new Exception('Status is required');
        }
        if (empty($country_code)) {
            throw new Exception('Country code is required');
        }
        return true;
    }

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
    ) {

        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (empty($password)) {
            throw new Exception('Password is required');
        }
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($phone)) {
            throw new Exception('Phone is required');
        }
        if (empty($delivery_address)) {
            // throw new Exception('Delivery address is required');
        }
        if (empty($role)) {
            throw new Exception('Role is required');
        }

        // role agent
        if ($role == 'agent') {
            if (empty($social_security_number)) {
                throw new Exception('Social security number is required');
            }
        }

        if (empty($status)) {
            throw new Exception('Status is required');
        }
        if (empty($country_code)) {
            throw new Exception('Country code is required');
        }
        return true;
    }

    public function validateUpdateProfile(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    ) {
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($phone)) {
            throw new Exception('Phone is required');
        }
        if (empty($delivery_address)) {
            throw new Exception('Delivery address is required');
        }
        return true;
    }

    /**
     * @param int $id
     * @return UserEntity
     */
    public function validateDelete(int $id)
    {
        if (empty($id)) {
            throw new Exception('ID is required');
        }
        throw new Exception('Delete not allowed');
        return false;
        $user = $this->userRepository->find($id);
        return $user;
    }

    public function validateUpdateUser(
        int $id,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    ) {
        $user = $this->userRepository->find($id);
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($phone)) {
            throw new Exception('Phone is required');
        }
        if (empty($delivery_address)) {
            throw new Exception('Delivery address is required');
        }
        if (empty($role)) {
            throw new Exception('Role is required');
        }
        if ($role == 'agent') {
            if (empty($social_security_number)) {
                throw new Exception('Social security number is required');
            }
        }
        if (empty($status)) {
            throw new Exception('Status is required');
        }
        if (empty($country_code)) {
            throw new Exception('Country code is required');
        }
        return $user;
    }

    public function validateUpdatePassword(int $id, string $password)
    {
        if (empty($password)) {
            throw new Exception('Password is required');
        }
        return true;
    }

    public function validateChangePassword(int $id, string $old_password, string $new_password, string $confirm_password)
    {
        if (empty($old_password)) {
            throw new Exception('Old password is required');
        }
        if (empty($new_password)) {
            throw new Exception('New password is required');
        }
        if (empty($confirm_password)) {
            throw new Exception('Confirm password is required');
        }
        $user = $this->userRepository->find($id);
        if (!password_verify($old_password, $user->password)) {
            throw new Exception('Old password is incorrect');
        }
        if ($new_password != $confirm_password) {
            throw new Exception('New password and confirm password do not match');
        }
        return true;
    }

    public function validateVerifyEmail(string $email, string $otp)
    {
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        if (empty($otp)) {
            throw new Exception('OTP is required');
        }
        $user = $this->userRepository->findBy('email', $email);
        if ($user->otp != $otp) {
            throw new Exception('OTP is incorrect');
        }
        return true;
    }

    public function validateResetPassword(
        string $email,
        string $otp,
        string $password,
        string $confirm_password
    ) {
        if(empty($email)){
            throw new Exception('Email is required');
        }
        if(empty($otp)){
            throw new Exception('OTP is required');
        }
        if(empty($password)){
            throw new Exception('Password is required');
        }
        if(empty($confirm_password)){
            throw new Exception('Confirm password is required');
        }
        if($password != $confirm_password){
            throw new Exception('Password and confirm password do not match');
        }
        $user = $this->userRepository->findBy('email', $email);
        if ($user->otp != $otp) {
            throw new Exception('OTP is incorrect');
        }
        return true;
    }

    public function validateTopUpWallet(int $id, float $amount) {
        if(empty($id)){
            throw new Exception('ID is required');
        }
        if(empty($amount)){
            throw new Exception('Amount is required');
        }
        $user = $this->userRepository->find($id);
    
        return $user;
    }

    public function validateWithdrawWallet(int $id, float $amount) {
        if(empty($id)){
            throw new Exception('ID is required');
        }
        if(empty($amount)){
            throw new Exception('Amount is required');
        }
        $user = $this->userRepository->find($id);
        if($user->wallet_balance < $amount){
            throw new Exception('Balance is not enough');
        }
        return $user;
    }
}
