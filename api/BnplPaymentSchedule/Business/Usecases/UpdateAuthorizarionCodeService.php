<?php

namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;

class UpdateAuthorizarionCodeService
{

    private GetAllSchedulesForOrder $getAllSchedulesForOrder;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    
    public function __construct(
        GetAllSchedulesForOrder $getAllSchedulesForOrder,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository
    ) {
        $this->getAllSchedulesForOrder = $getAllSchedulesForOrder;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
    }

    function execute(int $order_id, string $authorization_code){
        $schedules = $this->getAllSchedulesForOrder->execute($order_id)->fetchAll();
        foreach ($schedules as $schedule) {
            $schedule->authorization_code = $authorization_code;
            $this->bnplPaymentScheduleRepository->save($schedule);
        }
    }
}
