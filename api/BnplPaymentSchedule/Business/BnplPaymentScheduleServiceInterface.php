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

    /**
     *  return first schedule id
     * @param int $number_of_installment
     * @param float $installment_amount
     * @param string $reference
     * @param string $authorization_code
     * @return BnplPaymentScheduleEntity
     */
    public function createSchedules(
        int $order_id,
        int $number_of_installment,
        float $installment_amount,
        string $reference,
        string $authorization_code
    );

    /**
     * @param int $order_id
     * @return BnplPaymentScheduleEntity
     */
    public function getFirstSchedule(int $order_id);

    /**
     * @param int $order_id
     * @return BnplPaymentScheduleEntity
     */
    public function getNextSchedule(int $order_id);

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function isSchedulePaid(int $schedule_id);

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function isSchedulePending(int $schedule_id);

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function chargeSchedule(int $schedule_id);

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function increaseNumberOfAttempts(int $schedule_id);

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function currentDateIsPaymentDate(int $schedule_id);
}
