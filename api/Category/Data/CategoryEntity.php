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


    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->correctDateDefaults(empty($this->created_at) || empty($this->updated_at));
    }

    private function correctDateDefaults(bool $condition){
        if ($condition) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
        }
    }
}
