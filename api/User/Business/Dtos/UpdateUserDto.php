<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class UpdateUserDto
{
    public int $id;
    public string $name = '';
    public string $phone = '';
    public string $delivery_address = '';
    public string $social_security_number = '';
    public string $role = '';
    public string $status = '';
    public string $country_code = '';

    public function __construct(
        int $id,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    ) {
        Contracts::requires($id > 0, 'ID is required');
        Contracts::requiresNotNullOrEmpty($name, 'Name');
        Contracts::requiresNotNullOrEmpty($phone, 'Phone');
        Contracts::requiresNotNullOrEmpty($delivery_address, 'Delivery Address');
        Contracts::requiresNotNullOrEmpty($role, 'Role');
        Contracts::requiresInArray($role, ['customer', 'agent', 'staff', 'super-admin', 'admin'], 'Role');
        Contracts::requiresNotNullOrEmpty($status, 'Status');
        Contracts::requiresNotNullOrEmpty($country_code, 'Country Code');

        if ($role === 'agent') {
            Contracts::requiresNotNullOrEmpty($social_security_number, 'Social Security Number');
        }

        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->delivery_address = $delivery_address;
        $this->social_security_number = $social_security_number;
        $this->role = $role;
        $this->status = $status;
        $this->country_code = $country_code;
    }
}
