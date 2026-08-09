<?php

namespace Thread\Data;

use Shared\AbstractBaseRepository;
use Thread\Data\ThreadEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<ThreadEntity>
 */
class ThreadRepository extends AbstractBaseRepository implements ThreadRepositoryInterface
{
   protected string $table = 'threads';
   protected string $sql = "SELECT * FROM threads WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = ThreadEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);

      $this->addFilter('order_id', function($value, &$sql, &$params){
         $sql .= " AND order_id = :order_id";
         $params['order_id'] = $value;
      });

      $this->addAppliedFilter(function (string &$sql, array &$params) {
         $sql .= " ORDER BY created_at ASC ";
      });
   }

   public function query(array $filters = []){
      $this->sql = "SELECT * FROM {$this->table} WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      return new QueryObject($this->sql, $this->params, $this->db, $this->hydrateClass);
   }
}
