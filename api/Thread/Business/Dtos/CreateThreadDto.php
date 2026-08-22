<?php

namespace Thread\Business\Dtos;

use Shared\Contracts;

class CreateThreadDto
{
    public int $order_id;
    public int $sender_id;
    public string $message;
    public array $attachment_url;

    public function __construct(int $order_id, int $sender_id, string $message, array $attachment_url = [])
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($sender_id > 0, 'Sender ID is required');
        Contracts::requiresNotNullOrEmpty($message, 'Message');

        $this->order_id = $order_id;
        $this->sender_id = $sender_id;
        $this->message = $message;
        $this->attachment_url = $attachment_url;
    }
}
