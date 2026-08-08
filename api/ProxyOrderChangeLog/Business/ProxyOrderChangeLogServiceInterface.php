<?php

namespace ProxyOrderChangeLog\Business;

use Shared\AbstractBaseServiceInterface;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;

/**
 * @extends AbstractBaseServiceInterface<ProxyOrderChangeLogEntity>
 */
interface ProxyOrderChangeLogServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
    public function log(int $order_id, string $field_name, string $old_value, string $new_value);
}
