<?php

namespace SnappyOrder\Business\Dtos;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class ChangeStatusDto
{
    public int $order_id;
    public string $status;
    public string $pickup_otp_code;

    public function __construct(int $order_id, string $status, string $pickup_otp_code = '')
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresNotNullOrEmpty($status, 'Status');
        Contracts::requiresInArray($status, SnappyOrderRepositoryInterface::ALLOWED_STATUSES, 'status');

        $this->order_id = $order_id;
        $this->status = $status;
        $this->pickup_otp_code = $pickup_otp_code;
    }
}
