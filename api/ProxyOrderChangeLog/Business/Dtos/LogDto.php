<?php

namespace ProxyOrderChangeLog\Business\Dtos;

use Shared\Contracts;

class LogDto
{
    public int $order_id;
    public string $field_name;
    public string $old_value;
    public string $new_value;

    public function __construct(int $order_id, string $field_name, string $old_value, string $new_value)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresNotNullOrEmpty($field_name, 'Field Name');

        $this->order_id = $order_id;
        $this->field_name = $field_name;
        $this->old_value = $old_value;
        $this->new_value = $new_value;
    }
}
