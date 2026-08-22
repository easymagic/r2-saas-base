<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class UpdateProfileDto
{
    public int $id;
    public string $name = '';
    public string $phone = '';
    public string $delivery_address = '';

    public function __construct(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    ) {
        Contracts::requires($id > 0, 'ID is required');
        Contracts::requiresNotNullOrEmpty($name, 'Name');
        Contracts::requiresNotNullOrEmpty($phone, 'Phone');
        Contracts::requiresNotNullOrEmpty($delivery_address, 'Delivery Address');

        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->delivery_address = $delivery_address;
    }
}
