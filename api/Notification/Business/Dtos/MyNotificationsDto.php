<?php

namespace Notification\Business\Dtos;

use Shared\Contracts;

class MyNotificationsDto
{
    public int $userId;

    public function __construct(int $userId)
    {
        Contracts::requires($userId > 0, 'User ID is required');

        $this->userId = $userId;
    }
}
