<?php
namespace OrderItem\Business\Usecases;

use OrderItem\Data\OrderItemRepositoryInterface;
use Shared\Contracts;

class FetchForOrderService
{
    private OrderItemRepositoryInterface $orderItemRepository;

    public function __construct(OrderItemRepositoryInterface $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    public function query(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');

        return $this->orderItemRepository->query([
            'order_id' => $order_id,
        ]);
    }
}
