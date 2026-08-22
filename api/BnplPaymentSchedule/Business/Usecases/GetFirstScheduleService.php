<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use Shared\Contracts;

class GetFirstScheduleService
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;

    public function __construct(BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository)
    {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
    }

    public function query(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $schedules = $this->bnplPaymentScheduleRepository->query([
            'order_id' => $order_id,
        ])->fetchAll();
        Contracts::requires(!empty($schedules), 'No BNPL schedules found for this order');
        return $schedules[0];
    }
}
