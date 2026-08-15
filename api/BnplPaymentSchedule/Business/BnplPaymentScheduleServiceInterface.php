<?php

namespace BnplPaymentSchedule\Business;

use Shared\AbstractBaseServiceInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;

/**
 * @extends AbstractBaseServiceInterface<BnplPaymentScheduleEntity>
 */
interface BnplPaymentScheduleServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
}
