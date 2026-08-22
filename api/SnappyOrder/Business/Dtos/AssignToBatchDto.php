<?php

namespace SnappyOrder\Business\Dtos;

use Shared\Contracts;

class AssignToBatchDto
{
    public int $order_id;
    public int $batch_id;

    public function __construct(int $order_id, int $batch_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($batch_id > 0, 'Batch ID is required');

        $this->order_id = $order_id;
        $this->batch_id = $batch_id;
    }
}
