<?php

namespace EcomOrder\Data;

use Shared\AbstractBaseEntity;

class EcomOrderEntity extends AbstractBaseEntity
{
    public int $user_id = 0;
    public string $type = '';
    public int $number_of_installment = 0;
    public float $shipping_fee = 0;
    public float $service_charge = 0;
    public float $total_amount = 0;
    public int $is_guest = 0;
    public string $customer_name = '';
    public string $customer_address = '';
    public string $customer_email = '';
    public string $reference = '';
    public string $payment_status = '';
    public string $delivery_status = '';
    public int $agent_id = 0;
    public string $created_at = '';
    public string $updated_at = '';
    /** Transient Paystack checkout URL; not stored on ecom_orders. */
    public string $payment_url = '';

    public array $items = [];

    public array $schedules = [];

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
