<?php

namespace Batch\Data;

use Shared\AbstractBaseEntity;

class BatchEntity extends AbstractBaseEntity
{
    public string $name = '';
    public string $description = '';
    public string $created_at = '';
}
