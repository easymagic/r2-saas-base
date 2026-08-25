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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->handleNullAttributes(empty($this->created_at) || empty($this->updated_at));
    }

    private function handleNullAttributes(bool $condition){
        if($condition){
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
        }
    }
}
