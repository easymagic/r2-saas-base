<?php
namespace BnplPaymentSchedule\Business\Usecases;

class IsSchedulePaidService
{
    private BnplPaymentScheduleSupport $bnplPaymentScheduleSupport;

    public function __construct(BnplPaymentScheduleSupport $bnplPaymentScheduleSupport)
    {
        $this->bnplPaymentScheduleSupport = $bnplPaymentScheduleSupport;
    }

    public function query(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleSupport->requireSchedule($schedule_id);
        return $schedule->payment_status === 'paid';
    }
}
