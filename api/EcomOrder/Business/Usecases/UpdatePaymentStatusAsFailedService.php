<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Business\Usecases\Mail\SendOrderFailedNotificationToCustomerService;
use EcomOrder\Data\EcomOrderRepositoryInterface;

class UpdatePaymentStatusAsFailedService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderSupport $ecomOrderSupport;
    private SendOrderFailedNotificationToCustomerService $sendOrderFailedNotificationToCustomerService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderSupport $ecomOrderSupport,
        SendOrderFailedNotificationToCustomerService $sendOrderFailedNotificationToCustomerService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderSupport = $ecomOrderSupport;
        $this->sendOrderFailedNotificationToCustomerService = $sendOrderFailedNotificationToCustomerService;
    }

    public function execute(int $order_id)
    {
        $order = $this->ecomOrderSupport->requirePendingPaymentOrder($order_id);
        $order->payment_status = 'failed';
        $order = $this->ecomOrderRepository->save($order);
        $this->sendOrderFailedNotificationToCustomerService->execute($order_id);
        return $order;
    }
}
