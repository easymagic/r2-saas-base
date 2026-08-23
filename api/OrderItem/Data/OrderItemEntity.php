<?php

namespace OrderItem\Data;

use Shared\AbstractBaseEntity;

class OrderItemEntity extends AbstractBaseEntity
{
    public int $order_id = 0;
    public int $merchant_id = 0;
    public int $product_id = 0;
    public int $qty = 0;
    public float $total_line_amount = 0;
    public int $settled = 0;
    public float $percentage_to_platform = 0;
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
