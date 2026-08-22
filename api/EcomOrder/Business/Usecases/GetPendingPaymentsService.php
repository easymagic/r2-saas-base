<?php
namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Business\Usecases\ChargeScheduleService;
use BnplPaymentSchedule\Business\Usecases\IncreaseNumberOfAttemptsService;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;

class GetPendingPaymentsService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private ChargeScheduleService $chargeScheduleService;
    private IncreaseNumberOfAttemptsService $increaseNumberOfAttemptsService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        ChargeScheduleService $chargeScheduleService,
        IncreaseNumberOfAttemptsService $increaseNumberOfAttemptsService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->chargeScheduleService = $chargeScheduleService;
        $this->increaseNumberOfAttemptsService = $increaseNumberOfAttemptsService;
    }

    public function execute()
    {
        $dueSchedules = $this->bnplPaymentScheduleRepository->query([
            'payment_status' => 'pending',
            'due_on_or_before' => date('Y-m-d'),
        ])->fetchAll();

        foreach ($dueSchedules as $schedule) {
            if (trim((string) $schedule->authorization_code) === '') {
                continue;
            }
            try {
                $this->chargeScheduleService->execute((int) $schedule->id);
            } catch (\Exception $e) {
                $this->increaseNumberOfAttemptsService->execute((int) $schedule->id);
            }
        }

        return $this->ecomOrderRepository->query([
            'pending_payments' => 1,
        ]);
    }
}
