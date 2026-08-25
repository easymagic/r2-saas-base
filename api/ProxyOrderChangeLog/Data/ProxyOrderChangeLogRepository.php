<?php

namespace ProxyOrderChangeLog\Data;

use Shared\AbstractBaseRepository;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

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

      $this->addFilter("snappy_order_id", function ($value, string &$sql, array &$params) {
         $sql .= " AND snappy_order_id = :snappy_order_id";
         $params['snappy_order_id'] = (int) $value;
      });
      $this->addFilter("field_name", function (string $value, string &$sql, array &$params) {
         $sql .= " AND field_name = :field_name";
         $params['field_name'] = $value;
      });
      $this->addFilter("search", function (string $value, string &$sql, array &$params) {
         $sql .= " AND (field_name LIKE :search OR old_value LIKE :search OR new_value LIKE :search)";
         $params['search'] = '%' . $value . '%';
      });
   }

   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM proxy_order_change_log WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      $this->sql .= " ORDER BY id DESC";
      return new QueryObject(
         $this->sql,
         $this->params,
         $this->db,
         $this->hydrateClass
      );
   }
}
