<?php

namespace SnappyOrder\Data;

use Shared\AbstractBaseRepository;
use Shared\Query\QueryObject;
use SnappyOrder\Data\SnappyOrderEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<SnappyOrderEntity>
 */
class SnappyOrderRepository extends AbstractBaseRepository implements SnappyOrderRepositoryInterface
{
   protected string $table = 'snappy_orders';
   protected string $sql = "SELECT * FROM snappy_orders WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = SnappyOrderEntity::class;

   private DbServiceInterface $dbService;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
      $this->dbService = $db;

      $this->addFilter("status", function (string $value, string &$sql, array &$params) {
         $sql .= " AND status = :status";
         $params["status"] = $value;
      });

      $this->addFilter("agent_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND agent_id = :agent_id";
         $params["agent_id"] = $value;
      });

      $this->addFilter("user_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND user_id = :user_id";
         $params["user_id"] = $value;
      });

      $this->addFilter("search", function (string $value, string &$sql, array &$params) {
         $sql .= " AND (reference LIKE :search OR link LIKE :search OR description LIKE :search)";
         $params["search"] = "%" . $value . "%";
      });

      // batch_id
      $this->addFilter("batch_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND batch_id = :batch_id";
         $params["batch_id"] = $value;
      });

      $this->addAppliedFilter(function (string &$sql, array &$params) {
         $sql .= " ORDER BY created_at DESC ";
      });
   }

   /**
    * @param array $filters
    * @return QueryObject<SnappyOrderEntity>
    */
   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM {$this->table} WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      return new QueryObject($this->sql, $this->params, $this->dbService, $this->hydrateClass);
   }
}
