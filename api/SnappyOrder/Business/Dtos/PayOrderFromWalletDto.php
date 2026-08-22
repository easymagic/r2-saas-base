<?php

namespace SnappyOrder\Business\Dtos;

use Shared\Contracts;

class PayOrderFromWalletDto
{
    public int $order_id;
    public int $user_id;

    public function __construct(int $order_id, int $user_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($user_id > 0, 'User ID is required');

        $this->order_id = $order_id;
        $this->user_id = $user_id;
    }
}
