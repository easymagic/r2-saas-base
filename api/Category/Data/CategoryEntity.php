<?php

namespace Category\Data;

use Shared\AbstractBaseEntity;

class CategoryEntity extends AbstractBaseEntity
{
    public string $name = '';
    public int $parent_id = 0;
    public string $description = '';
    public int $active = 1;
    public string $image = '';
    public string $slug = '';
    public string $created_at = '';
    public string $updated_at = '';
}
