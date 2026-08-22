<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class UpdatePasswordDto
{
    public int $id;
    public string $password;

    public function __construct(int $id, string $password)
    {
        Contracts::requires($id > 0, 'ID is required');
        Contracts::requiresNotNullOrEmpty($password, 'Password');

        $this->id = $id;
        $this->password = $password;
    }
}
