<?php
namespace BnplPaymentSchedule\Business\Usecases;

class CurrentDateIsPaymentDateService
{
    private BnplPaymentScheduleSupport $bnplPaymentScheduleSupport;

    public function __construct(BnplPaymentScheduleSupport $bnplPaymentScheduleSupport)
    {
        $this->bnplPaymentScheduleSupport = $bnplPaymentScheduleSupport;
    }

    public function query(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleSupport->requireSchedule($schedule_id);
        return date('Y-m-d', strtotime($schedule->expected_payment_date)) === date('Y-m-d');
    }
}
