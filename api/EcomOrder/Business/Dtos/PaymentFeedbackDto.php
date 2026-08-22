<?php

namespace EcomOrder\Business\Dtos;

use Shared\Contracts;

class PaymentFeedbackDto
{
    public int $order_id;
    public string $reference;

    public function __construct(int $order_id, string $reference)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresNotNullOrEmpty($reference, 'Reference');

        $this->order_id = $order_id;
        $this->reference = $reference;
    }
}
