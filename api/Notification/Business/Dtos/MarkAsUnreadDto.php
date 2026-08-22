<?php

namespace Notification\Business\Dtos;

use Shared\Contracts;

class MarkAsUnreadDto
{
    public int $notificationId;
    public int $userId;

    public function __construct(int $notificationId, int $userId)
    {
        Contracts::requires($notificationId > 0, 'Notification ID is required');
        Contracts::requires($userId > 0, 'User ID is required');

        $this->notificationId = $notificationId;
        $this->userId = $userId;
    }
}
