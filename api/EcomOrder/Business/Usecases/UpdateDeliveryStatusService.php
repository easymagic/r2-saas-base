<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Business\Dtos\UpdateDeliveryStatusDto;
use EcomOrder\Business\Usecases\Mail\SendOrderStatusChangedNotificationToCustomerService;
use EcomOrder\Business\Usecases\Mail\SendOrderStatusChangedNotificationToMerchantService;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;

class UpdateDeliveryStatusService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private SendOrderStatusChangedNotificationToCustomerService $sendOrderStatusChangedNotificationToCustomerService;
    private SendOrderStatusChangedNotificationToMerchantService $sendOrderStatusChangedNotificationToMerchantService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        SendOrderStatusChangedNotificationToCustomerService $sendOrderStatusChangedNotificationToCustomerService,
        SendOrderStatusChangedNotificationToMerchantService $sendOrderStatusChangedNotificationToMerchantService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->sendOrderStatusChangedNotificationToCustomerService = $sendOrderStatusChangedNotificationToCustomerService;
        $this->sendOrderStatusChangedNotificationToMerchantService = $sendOrderStatusChangedNotificationToMerchantService;
    }

    public function execute(UpdateDeliveryStatusDto $updateDeliveryStatusDto)
    {
        $order_id = $updateDeliveryStatusDto->order_id;
        $delivery_status = trim($updateDeliveryStatusDto->delivery_status);

        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresInArray(
            $delivery_status,
            ['pending', 'picked-up', 'on-the-way', 'delivered'],
            'delivery_status'
        );

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $order->delivery_status = $delivery_status;
        $order = $this->ecomOrderRepository->save($order);

        $this->sendOrderStatusChangedNotificationToCustomerService->execute($order_id, $delivery_status);
        $this->sendOrderStatusChangedNotificationToMerchantService->execute($order_id, $delivery_status);

        return $order;
    }
}
