<?php 

namespace Domain\User\Exceptions;

use Exception;
use R2Packages\Framework\Infrastructure\Framework\Exceptions\AppException;

class UserEmailNotFoundException extends AppException
{
    public function __construct(string $email)
    {
        parent::__construct("User email $email not found");
    }

    public static function forEmail(string $email)
    {
        return new static("User email $email not found");
    }
}