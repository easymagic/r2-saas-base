<?php

namespace EcomOrder\Data;

use Shared\AbstractBaseRepository;
use EcomOrder\Data\EcomOrderEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<EcomOrderEntity>
 */
class EcomOrderRepository extends AbstractBaseRepository implements EcomOrderRepositoryInterface
{
   protected string $table = 'ecom_orders';
   protected string $sql = "SELECT * FROM ecom_orders WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = EcomOrderEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
   }
}
