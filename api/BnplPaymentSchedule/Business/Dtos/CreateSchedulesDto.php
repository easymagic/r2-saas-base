<?php

namespace BnplPaymentSchedule\Business\Dtos;

use Shared\Contracts;

class CreateSchedulesDto
{
    public int $order_id;
    public int $number_of_installment;
    public float $installment_amount;
    public string $reference;
    public string $authorization_code;

    public function __construct(
        int $order_id,
        int $number_of_installment,
        float $installment_amount,
        string $reference,
        string $authorization_code
    ) {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($number_of_installment > 0, 'Number of installment must be greater than 0');
        Contracts::requires($installment_amount > 0, 'Installment amount must be greater than 0');
        $reference = trim($reference);
        Contracts::requiresNotNullOrEmpty($reference, 'reference');

        $this->order_id = $order_id;
        $this->number_of_installment = $number_of_installment;
        $this->installment_amount = $installment_amount;
        $this->reference = $reference;
        $this->authorization_code = $authorization_code;
    }
}
