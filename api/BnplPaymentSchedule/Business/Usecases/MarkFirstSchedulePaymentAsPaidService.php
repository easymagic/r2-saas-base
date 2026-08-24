<?php 
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;

class MarkFirstSchedulePaymentAsPaidService
{

    private GetFirstScheduleService $getFirstScheduleService;

    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;

    public function __construct(
        GetFirstScheduleService $getFirstScheduleService,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository
    ) {
        $this->getFirstScheduleService = $getFirstScheduleService;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
    }

    public function execute(int $order_id)
    {
        $schedule = $this->getFirstScheduleService->query($order_id);
        $schedule->payment_status = 'paid';
        $this->bnplPaymentScheduleRepository->save($schedule);
    }
}