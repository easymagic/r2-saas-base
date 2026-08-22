<?php

namespace User\Business\Dtos;

use Shared\Contracts;

class ChangePasswordDto
{
    public int $id;
    public string $old_password;
    public string $new_password;
    public string $confirm_password;

    public function __construct(
        int $id,
        string $old_password,
        string $new_password,
        string $confirm_password
    ) {
        Contracts::requires($id > 0, 'ID is required');
        Contracts::requiresNotNullOrEmpty($old_password, 'Old Password');
        Contracts::requiresNotNullOrEmpty($new_password, 'New Password');
        Contracts::requiresNotNullOrEmpty($confirm_password, 'Confirm Password');
        Contracts::requires($new_password === $confirm_password, 'New password and confirm password do not match');

        $this->id = $id;
        $this->old_password = $old_password;
        $this->new_password = $new_password;
        $this->confirm_password = $confirm_password;
    }
}
