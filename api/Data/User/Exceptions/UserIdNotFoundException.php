<?php 

namespace Data\User\Exceptions;

use Exception;
use R2Packages\Framework\Infrastructure\Framework\Exceptions\AppException;

class UserIdNotFoundException extends AppException
{
    public function __construct(int $userId)
    {
        parent::__construct("User ID $userId not found");
    }

    public static function forId(int $userId)
    {
        return new static("User ID $userId not found");
    }
}