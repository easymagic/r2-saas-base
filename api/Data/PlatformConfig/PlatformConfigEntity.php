<?php 
namespace Data\PlatformConfig;

use Data\AbstractBaseEntity;

class PlatformConfigEntity extends AbstractBaseEntity
{
    public int $id;
    public string $setting_key;
    public string $setting_value;
    public string $created_at;
    public string $updated_at;
}