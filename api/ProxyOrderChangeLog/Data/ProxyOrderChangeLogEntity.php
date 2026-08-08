<?php

namespace ProxyOrderChangeLog\Data;

use Shared\AbstractBaseEntity;

class ProxyOrderChangeLogEntity extends AbstractBaseEntity
{
    public int $snappy_order_id = 0;
    public string $field_name = '';
    public string $old_value = '';
    public string $new_value = '';
    public string $created_at = '';
    public string $updated_at = '';
}
