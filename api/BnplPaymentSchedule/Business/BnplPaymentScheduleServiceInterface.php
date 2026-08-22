<?php

namespace BnplPaymentSchedule\Business;

use Shared\AbstractBaseServiceInterface;
use BnplPaymentSchedule\Business\Dtos\CreateSchedulesDto;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;

/**
 * @extends AbstractBaseServiceInterface<BnplPaymentScheduleEntity>
 */
interface BnplPaymentScheduleServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param CreateSchedulesDto $createSchedulesDto
     * @return BnplPaymentScheduleEntity
     */
    public function createSchedules(CreateSchedulesDto $createSchedulesDto);

    public function getFirstSchedule(int $order_id);

    public function getNextSchedule(int $order_id);

    public function isSchedulePaid(int $schedule_id);

    public function isSchedulePending(int $schedule_id);

    public function chargeSchedule(int $schedule_id);

    public function increaseNumberOfAttempts(int $schedule_id);

    public function currentDateIsPaymentDate(int $schedule_id);
}
