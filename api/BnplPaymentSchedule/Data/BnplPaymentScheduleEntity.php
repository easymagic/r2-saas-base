<?php

namespace BnplPaymentSchedule\Data;

use Shared\AbstractBaseEntity;

class BnplPaymentScheduleEntity extends AbstractBaseEntity
{
    public int $order_id = 0;
    public float $installment_amount = 0;
    public string $payment_status = '';
    public string $reference = '';
    public string $authorization_code = '';
    public int $number_of_attempts = 0;
    public string $expected_payment_date = '';
    public string $paid_at = '';
    public string $created_at = '';
    public string $updated_at = '';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->correctDateDefaults(empty($this->created_at) || empty($this->updated_at) || empty($this->paid_at));
    }

    private function correctDateDefaults(bool $condition){
        if ($condition) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
            $this->paid_at = date('Y-m-d H:i:s');
        }
    }
}
