<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToCustomerService;
use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToMerchantService;
use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToPlatformService;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;

class BnplPaymentScheduleSupport
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private SendOrderPaidNotificationToCustomerService $sendOrderPaidNotificationToCustomerService;
    private SendOrderPaidNotificationToMerchantService $sendOrderPaidNotificationToMerchantService;
    private SendOrderPaidNotificationToPlatformService $sendOrderPaidNotificationToPlatformService;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        SendOrderPaidNotificationToCustomerService $sendOrderPaidNotificationToCustomerService,
        SendOrderPaidNotificationToMerchantService $sendOrderPaidNotificationToMerchantService,
        SendOrderPaidNotificationToPlatformService $sendOrderPaidNotificationToPlatformService
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->sendOrderPaidNotificationToCustomerService = $sendOrderPaidNotificationToCustomerService;
        $this->sendOrderPaidNotificationToMerchantService = $sendOrderPaidNotificationToMerchantService;
        $this->sendOrderPaidNotificationToPlatformService = $sendOrderPaidNotificationToPlatformService;
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
            $this->sendOrderPaidNotificationToCustomerService->execute($order_id);
            $this->sendOrderPaidNotificationToMerchantService->execute($order_id);
            $this->sendOrderPaidNotificationToPlatformService->execute($order_id);
            return;
        }

        $order->payment_status = 'part-paid';
        $this->ecomOrderRepository->save($order);
    }
}
