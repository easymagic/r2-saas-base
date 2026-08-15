<?php

namespace BnplPaymentSchedule\Data;

use Shared\AbstractBaseRepository;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

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
   }
}
