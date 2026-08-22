<?php

namespace Notification\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public int $userId;
    public string $title;
    public string $message;

    public function __construct(int $userId, string $title, string $message)
    {
        Contracts::requires($userId > 0, 'User ID is required');
        Contracts::requiresNotNullOrEmpty($title, 'Title');
        Contracts::requiresNotNullOrEmpty($message, 'Message');

        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
    }
}
