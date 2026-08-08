<?php

namespace ProxyOrderChangeLog\Data;

use Shared\AbstractBaseRepository;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<ProxyOrderChangeLogEntity>
 */
class ProxyOrderChangeLogRepository extends AbstractBaseRepository implements ProxyOrderChangeLogRepositoryInterface
{
   protected string $table = 'proxy_order_change_log';
   protected string $sql = "SELECT * FROM proxy_order_change_log WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = ProxyOrderChangeLogEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
   }
}
