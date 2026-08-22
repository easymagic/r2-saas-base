<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;

class IncreaseNumberOfAttemptsService
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private BnplPaymentScheduleSupport $bnplPaymentScheduleSupport;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        BnplPaymentScheduleSupport $bnplPaymentScheduleSupport
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->bnplPaymentScheduleSupport = $bnplPaymentScheduleSupport;
    }

    public function execute(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleSupport->requireSchedule($schedule_id);
        $schedule->number_of_attempts = (int) $schedule->number_of_attempts + 1;
        $this->bnplPaymentScheduleRepository->save($schedule);
        return true;
    }
}
