<?php

namespace Product\Data;

use Shared\AbstractBaseEntity;

class ProductEntity extends AbstractBaseEntity
{
    public string $name = '';
    public string $description = '';
    public string $image = '';
    public float $price = 0;
    public string $uuid = '';
    public float $old_price = 0;
    public int $stock_qty = 0;
    public int $active = 1;
    public string $slug = '';
    public int $user_id = 0;
    public int $category_id = 0;
    public string $created_at = '';
    public string $updated_at = '';
    // product image up to 7
    public string $image_1 = '';
    public string $image_2 = '';
    public string $image_3 = '';
    public string $image_4 = '';
    public string $image_5 = '';
    public string $image_6 = '';
    public string $image_7 = '';

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
