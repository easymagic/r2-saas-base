<?php
namespace OrderItem\Business\Usecases;

use Exception;
use OrderItem\Data\OrderItemEntity;
use OrderItem\Data\OrderItemRepositoryInterface;
use Shared\Contracts;

class OrderItemSupport
{
    private OrderItemRepositoryInterface $orderItemRepository;

    public function __construct(OrderItemRepositoryInterface $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }

    public function requireOrderItem(int $order_item_id)
    {
        Contracts::requires($order_item_id > 0, 'Order item ID is required');
        $orderItem = $this->orderItemRepository->find($order_item_id);
        Contracts::requireEntityFound($orderItem, 'Order item');
        return $orderItem;
    }

    public function platformShare(OrderItemEntity $orderItem)
    {
        return (float) $orderItem->total_line_amount * ((float) $orderItem->percentage_to_platform / 100);
    }

    public function merchantShare(OrderItemEntity $orderItem)
    {
        return (float) $orderItem->total_line_amount - $this->platformShare($orderItem);
    }

    public function normalizeDateBound(string $value, bool $endOfDay)
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new Exception($endOfDay ? 'date_to is invalid' : 'date_from is invalid');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', $timestamp) . ($endOfDay ? ' 23:59:59' : ' 00:00:00');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
