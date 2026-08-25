<?php

namespace Log\Data;

use Shared\AbstractBaseEntity;

class LogEntity extends AbstractBaseEntity
{
    public string $title = '';
    public string $payload = '';
    public string $response = '';
    public string $type = '';
    public string $created_at = '';
    public string $updated_at = '';
}
