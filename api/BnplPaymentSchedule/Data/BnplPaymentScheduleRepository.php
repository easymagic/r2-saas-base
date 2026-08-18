<?php

namespace BnplPaymentSchedule\Data;

use Shared\AbstractBaseRepository;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<BnplPaymentScheduleEntity>
 */
class BnplPaymentScheduleRepository extends AbstractBaseRepository implements BnplPaymentScheduleRepositoryInterface
{
   protected string $table = 'bnpl_payment_schedules';
   protected string $sql = "SELECT * FROM bnpl_payment_schedules WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = BnplPaymentScheduleEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);

      $this->addFilter("order_id", function (int $value, string &$sql, array &$params) {
         $sql .= " AND order_id = :order_id ";
         $params['order_id'] = $value;
      });
      $this->addFilter("payment_status", function (string $value, string &$sql, array &$params) {
         $sql .= " AND payment_status = :payment_status ";
         $params['payment_status'] = $value;
      });
      $this->addFilter("reference", function (string $value, string &$sql, array &$params) {
         $sql .= " AND reference = :reference ";
         $params['reference'] = $value;
      });
      $this->addFilter("expected_payment_date", function (string $value, string &$sql, array &$params) {
         $sql .= " AND expected_payment_date = :expected_payment_date ";
         $params['expected_payment_date'] = $value;
      });
      $this->addFilter("due_on_or_before", function (string $value, string &$sql, array &$params) {
         $sql .= " AND expected_payment_date <= :due_on_or_before ";
         $params['due_on_or_before'] = $value;
      });
      $this->addAppliedFilter(function (string &$sql, array &$params) {
         $sql .= " ORDER BY expected_payment_date ASC, id ASC ";
      });
   }

   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM bnpl_payment_schedules WHERE 1=1 ";
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
