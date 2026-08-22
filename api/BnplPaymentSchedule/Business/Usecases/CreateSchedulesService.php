<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Business\Dtos\CreateSchedulesDto;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;

class CreateSchedulesService
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
    }

    public function execute(CreateSchedulesDto $createSchedulesDto)
    {
        $order = $this->ecomOrderRepository->find($createSchedulesDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $first = null;
        for ($i = 0; $i < $createSchedulesDto->number_of_installment; $i++) {
            $schedule = $this->bnplPaymentScheduleRepository->save(new BnplPaymentScheduleEntity([
                'order_id' => $createSchedulesDto->order_id,
                'installment_amount' => $createSchedulesDto->installment_amount,
                'payment_status' => 'pending',
                'reference' => $createSchedulesDto->reference,
                'authorization_code' => $createSchedulesDto->authorization_code,
                'number_of_attempts' => 0,
                'expected_payment_date' => date('Y-m-d', strtotime('+' . $i . ' month')),
            ]));
            if ($first === null) {
                $first = $schedule;
            }
        }

        return $first;
    }
}
