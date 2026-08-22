<?php

namespace PlatformConfig\Data;

use Shared\AbstractBaseEntity;

class PlatformConfigEntity extends AbstractBaseEntity
{
    public string $setting_key = '';
    public string $setting_value = '';
    public string $created_at = '';
    public string $updated_at = '';
}
