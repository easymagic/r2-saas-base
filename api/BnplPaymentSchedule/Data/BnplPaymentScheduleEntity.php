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
    public string $created_at = '';
    public string $updated_at = '';
}
