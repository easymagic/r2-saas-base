<?php 

namespace Domain\User;

use Domain\AbstractBaseEntity;

class UserEntity extends AbstractBaseEntity
{
    public string $name;
    public string $email;
    public string $password;
    public string $phone;
    public string $country_code;
    public string $role;
    public string $status;
    public string $created_at;
    public string $updated_at;
    public string $otp;
    public float $wallet_balance;
    public string $social_security_number;
    public string $token;
    public string $delivery_address;
    public string $email_verified_at;


    function isAdmin(){
        return strpos($this->role, 'admin') !== false;
    }

    function isCustomer(){
        return strpos($this->role, 'customer') !== false;
    }

    function isStaff(){
        return strpos($this->role, 'staff') !== false;
    }

}