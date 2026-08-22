<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class RegisterDto
{
    public string $email = '';
    public string $password = '';
    public string $name = '';
    public string $phone = '';
    public string $delivery_address = '';
    public string $social_security_number = '';
    public string $role = 'customer';
    public string $status = 'inactive';
    public string $country_code = '';

    // string $email,
    // string $password,
    // string $name,
    // string $phone,
    // string $delivery_address,
    // string $social_security_number,
    // string $role,
    // string $status,
    // string $country_code,

    public function __construct(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
    ) {
        Contracts::requiresNotNullOrEmpty($email, 'Email');
        Contracts::requiresNotNullOrEmpty($password, 'Password');
        Contracts::requiresNotNullOrEmpty($name, 'Name');
        Contracts::requiresNotNullOrEmpty($phone, 'Phone');
        Contracts::requiresNotNullOrEmpty($delivery_address, 'Delivery Address');
        Contracts::requiresNotNullOrEmpty($social_security_number, 'Social Security Number');
        Contracts::requiresInArray($role, ['customer', 'agent'], 'Role');


        Contracts::requires(filter_var($email, FILTER_VALIDATE_EMAIL), 'Email must be a valid email address');

        Contracts::requires(strlen($password) >= 8, 'Password must be at least 8 characters long');

        if ($role === 'agent') {
           Contracts::requiresNotNullOrEmpty($social_security_number, 'Social Security Number');
           Contracts::requires(strlen($social_security_number) === 10, 'Social Security Number must be 10 characters long');
        }

        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->phone = $phone;
        $this->delivery_address = $delivery_address;
        $this->social_security_number = $social_security_number;
        $this->role = $role;
        
        
    }
}
