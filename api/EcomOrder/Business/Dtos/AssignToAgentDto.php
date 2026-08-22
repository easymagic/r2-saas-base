<?php

namespace EcomOrder\Business\Dtos;

use Shared\Contracts;

class AssignToAgentDto
{
    public int $order_id;
    public int $agent_id;

    public function __construct(int $order_id, int $agent_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($agent_id > 0, 'Agent ID is required');

        $this->order_id = $order_id;
        $this->agent_id = $agent_id;
    }
}
