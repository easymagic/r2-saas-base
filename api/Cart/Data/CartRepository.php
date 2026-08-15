<?php

namespace Cart\Data;

use Shared\AbstractBaseRepository;
use Cart\Data\CartEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<CartEntity>
 */
class CartRepository extends AbstractBaseRepository implements CartRepositoryInterface
{
   protected string $table = 'carts';
   protected string $sql = "SELECT * FROM carts WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = CartEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
      $this->addFilter("uuid", function (mixed $value, string &$sql, array &$params) {
         $sql .= " AND cart_sess_uuid = :uuid";
         $params['uuid'] = $value;
      });
      $this->addFilter("product_id", function (mixed $value, string &$sql, array &$params) {
         $sql .= " AND product_id = :product_id";
         $params['product_id'] = $value;
      });
   }

   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM carts WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      return new QueryObject(
         $this->sql,
         $this->params,
         $this->db,
         $this->hydrateClass
      );
   }
}
