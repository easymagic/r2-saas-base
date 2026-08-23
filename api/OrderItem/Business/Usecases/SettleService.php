<?php
namespace OrderItem\Business\Usecases;

use OrderItem\Business\Usecases\Mail\NotifyMerchantOfSettlementService;
use OrderItem\Business\Usecases\Mail\NotifyPlatformOfSettlementService;
use OrderItem\Data\OrderItemRepositoryInterface;
use Shared\Contracts;

class SettleService
{
    private OrderItemRepositoryInterface $orderItemRepository;
    private OrderItemSupport $orderItemSupport;
    private NotifyMerchantOfSettlementService $notifyMerchantOfSettlementService;
    private NotifyPlatformOfSettlementService $notifyPlatformOfSettlementService;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemSupport $orderItemSupport,
        NotifyMerchantOfSettlementService $notifyMerchantOfSettlementService,
        NotifyPlatformOfSettlementService $notifyPlatformOfSettlementService
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemSupport = $orderItemSupport;
        $this->notifyMerchantOfSettlementService = $notifyMerchantOfSettlementService;
        $this->notifyPlatformOfSettlementService = $notifyPlatformOfSettlementService;
    }

    public function execute(int $order_item_id)
    {
        $orderItem = $this->orderItemSupport->requireOrderItem($order_item_id);
        Contracts::requires((int) $orderItem->settled !== 1, 'Order item is already settled');

        $orderItem->settled = 1;
        $this->orderItemRepository->save($orderItem);

        $this->notifyMerchantOfSettlementService->execute((int) $orderItem->id);
        $this->notifyPlatformOfSettlementService->execute((int) $orderItem->id);

        return true;
    }
}
