<?php

namespace EcomOrder\Data;

use Shared\AbstractBaseRepository;
use EcomOrder\Data\EcomOrderEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

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

      $this->addFilter("user_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND user_id = :user_id ";
         $params['user_id'] = $value;
      });
      $this->addFilter("agent_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND agent_id = :agent_id ";
         $params['agent_id'] = $value;
      });
      $this->addFilter("type", function (string $value, string &$sql, array &$params) {
         $sql .= " AND type = :type ";
         $params['type'] = $value;
      });
      $this->addFilter("payment_status", function (string $value, string &$sql, array &$params) {
         $sql .= " AND payment_status = :payment_status ";
         $params['payment_status'] = $value;
      });
      $this->addFilter("delivery_status", function (string $value, string &$sql, array &$params) {
         $sql .= " AND delivery_status = :delivery_status ";
         $params['delivery_status'] = $value;
      });
      $this->addFilter("reference", function (string $value, string &$sql, array &$params) {
         $sql .= " AND reference = :reference ";
         $params['reference'] = $value;
      });
      $this->addFilter("pending_payments", function (bool $value, string &$sql, array &$params) {
         $sql .= " AND payment_status IN ('pending') ";
      });
      // $this->addFilter("pending_payments", function ($value, string &$sql, array &$params) {
      //    $sql .= " AND payment_status IN ('pending','part-paid') ";
      // });
      $this->addFilter("card_payments", function ($value, string &$sql, array &$params) {
         $sql .= " AND type IN ('card','bnpl') ";
      });
      $this->addFilter("search", function (string $value, string &$sql, array &$params) {
         $sql .= " AND (reference LIKE :search OR customer_name LIKE :search OR customer_email LIKE :search) ";
         $params['search'] = "%" . $value . "%";
      });
      $this->addFilter("date_from", function (string $value, string &$sql, array &$params) {
         $sql .= " AND created_at >= :date_from ";
         $params['date_from'] = $value;
      });
      $this->addFilter("date_to", function (string $value, string &$sql, array &$params) {
         $sql .= " AND created_at <= :date_to ";
         $params['date_to'] = $value;
      });
      $this->addAppliedFilter(function (string &$sql, array &$params) {
         $sql .= " ORDER BY created_at DESC ";
      });
   }

   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM ecom_orders WHERE 1=1 ";
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
