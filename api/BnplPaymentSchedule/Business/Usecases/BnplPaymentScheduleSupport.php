<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Business\EcomOrderNotificationServiceInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;

class BnplPaymentScheduleSupport
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderNotificationServiceInterface $ecomOrderNotificationService;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderNotificationServiceInterface $ecomOrderNotificationService
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderNotificationService = $ecomOrderNotificationService;
    }

    public function requireSchedule(int $schedule_id)
    {
        Contracts::requires($schedule_id > 0, 'Schedule ID is required');
        $schedule = $this->bnplPaymentScheduleRepository->find($schedule_id);
        Contracts::requireEntityFound($schedule, 'BNPL schedule');
        return $schedule;
    }

    public function failScheduleIfMaxAttempts(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleRepository->find($schedule_id);
        if ((int) $schedule->number_of_attempts >= 3) {
            $schedule->payment_status = 'failed';
            $this->bnplPaymentScheduleRepository->save($schedule);
        }
    }

    public function refreshOrderPaymentStatus(int $order_id)
    {
        $pending = $this->bnplPaymentScheduleRepository->query([
            'order_id' => $order_id,
            'payment_status' => 'pending',
        ])->fetchAll();

        $order = $this->ecomOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            return;
        }

        if (empty($pending)) {
            $order->payment_status = 'paid';
            $this->ecomOrderRepository->save($order);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToCustomer($order_id);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToMerchant($order_id);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToPlatform($order_id);
            return;
        }

        $order->payment_status = 'part-paid';
        $this->ecomOrderRepository->save($order);
    }
}
