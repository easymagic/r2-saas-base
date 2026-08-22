<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToCustomerService;
use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToMerchantService;
use EcomOrder\Business\Usecases\Mail\SendOrderPaidNotificationToPlatformService;
use EcomOrder\Data\EcomOrderRepositoryInterface;

class UpdatePaymentStatusAsPaidService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderSupport $ecomOrderSupport;
    private SendOrderPaidNotificationToCustomerService $sendOrderPaidNotificationToCustomerService;
    private SendOrderPaidNotificationToMerchantService $sendOrderPaidNotificationToMerchantService;
    private SendOrderPaidNotificationToPlatformService $sendOrderPaidNotificationToPlatformService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderSupport $ecomOrderSupport,
        SendOrderPaidNotificationToCustomerService $sendOrderPaidNotificationToCustomerService,
        SendOrderPaidNotificationToMerchantService $sendOrderPaidNotificationToMerchantService,
        SendOrderPaidNotificationToPlatformService $sendOrderPaidNotificationToPlatformService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderSupport = $ecomOrderSupport;
        $this->sendOrderPaidNotificationToCustomerService = $sendOrderPaidNotificationToCustomerService;
        $this->sendOrderPaidNotificationToMerchantService = $sendOrderPaidNotificationToMerchantService;
        $this->sendOrderPaidNotificationToPlatformService = $sendOrderPaidNotificationToPlatformService;
    }

    public function execute(int $order_id)
    {
        $order = $this->ecomOrderSupport->requirePendingPaymentOrder($order_id);
        $order->payment_status = 'paid';
        $order = $this->ecomOrderRepository->save($order);

        $this->sendOrderPaidNotificationToCustomerService->execute($order_id);
        $this->sendOrderPaidNotificationToMerchantService->execute($order_id);
        $this->sendOrderPaidNotificationToPlatformService->execute($order_id);

        return $order;
    }
}
