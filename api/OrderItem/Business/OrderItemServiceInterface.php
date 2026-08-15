<?php

namespace OrderItem\Business;

use Shared\AbstractBaseServiceInterface;
use OrderItem\Data\OrderItemEntity;

/**
 * @extends AbstractBaseServiceInterface<OrderItemEntity>
 */
interface OrderItemServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
}
