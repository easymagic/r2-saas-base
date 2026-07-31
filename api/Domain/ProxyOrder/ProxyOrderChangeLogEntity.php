<?php 
namespace Domain\ProxyOrder;

class ProxyOrderChangeLogEntity
{
    public int $id;
    public int $proxy_order_id;
    public string $field_name;
    public string $old_value;
    public string $new_value;
    public string $created_at;
    public string $updated_at;
}