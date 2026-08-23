<?php

namespace BnplPaymentSchedule\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<BnplPaymentScheduleEntity>
 */
interface BnplPaymentScheduleRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * @param array $filters
     * @return QueryObject<BnplPaymentScheduleEntity>
     */
    public function query(array $filters = []);
}
