<?php

namespace OrderItem\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;
/**
 * @extends AbstractBaseRepositoryInterface<OrderItemEntity>
 */
interface OrderItemRepositoryInterface extends AbstractBaseRepositoryInterface
{
   /**
    * @param array $params
    * @return QueryObject
    */ 
   public function query(array $params = []);
}
