<?php

namespace OrderItem\Data;

use Shared\AbstractBaseRepository;
use OrderItem\Data\OrderItemEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

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

      $this->addFilter("order_id",function(int $value, string &$sql, array &$params){
         $sql .= " AND order_id = :order_id ";
         $params['order_id'] = $value;
      });
      $this->addFilter("merchant_id",function(int $value, string &$sql, array &$params){
         $sql .= " AND merchant_id = :merchant_id ";
         $params['merchant_id'] = $value;
      });
      $this->addFilter("product_id",function(int $value, string &$sql, array &$params){
         $sql .= " AND product_id = :product_id ";
         $params['product_id'] = $value;
      });
      $this->addFilter("settled",function(int $value, string &$sql, array &$params){
         $sql .= " AND settled = :settled ";
         $params['settled'] = $value;
      });
      $this->addFilter("percentage_to_platform",function(float $value, string &$sql, array &$params){
         $sql .= " AND percentage_to_platform = :percentage_to_platform ";
         $params['percentage_to_platform'] = $value;
      });
      $this->addFilter("date_from",function(string $value, string &$sql, array &$params){
         $sql .= " AND created_at >= :date_from ";
         $params['date_from'] = $value;
      });
      $this->addFilter("date_to",function(string $value, string &$sql, array &$params){
         $sql .= " AND created_at <= :date_to ";
         $params['date_to'] = $value;
      });
   }

   public function query(array $params = [])
   {
      $this->sql = "SELECT * FROM order_items WHERE 1=1 ";
      $this->params = [];
      $this->filter($params);
      return new QueryObject(
         $this->sql,
         $this->params,
         $this->db,
         $this->hydrateClass
      );
   }
}
