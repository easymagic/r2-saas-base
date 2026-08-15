<?php

namespace OrderItem\Data;

use Shared\AbstractBaseRepository;
use OrderItem\Data\OrderItemEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<OrderItemEntity>
 */
class OrderItemRepository extends AbstractBaseRepository implements OrderItemRepositoryInterface
{
   protected string $table = 'order_items';
   protected string $sql = "SELECT * FROM order_items WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = OrderItemEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
   }
}
