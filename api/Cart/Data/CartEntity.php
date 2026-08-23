<?php

namespace Cart\Data;

use Shared\AbstractBaseEntity;

class CartEntity extends AbstractBaseEntity
{
    public string $cart_sess_uuid = '';
    public int $merchant_id = 0;
    public int $product_id = 0;
    public int $qty = 0;
    public float $price_total = 0;
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
