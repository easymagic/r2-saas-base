<?php 
namespace Domain\PlatformConfig;

class PlatformConfigEntity
{
    public int $id;
    public string $key;
    public string $value;
    public string $created_at;
    public string $updated_at;
}