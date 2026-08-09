<?php

namespace Batch\Data;

use Shared\AbstractBaseRepository;
use Batch\Data\BatchEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<BatchEntity>
 */
class BatchRepository extends AbstractBaseRepository implements BatchRepositoryInterface
{
   protected string $table = 'batches';
   protected string $sql = "SELECT * FROM batches WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = BatchEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
   }

   public function query(array $filters = []){
      $this->sql = "SELECT * FROM {$this->table} WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      return new QueryObject($this->sql, $this->params, $this->db, $this->hydrateClass);
   }
}
