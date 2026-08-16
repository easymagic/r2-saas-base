<?php

namespace OrderItem\Business;

use Shared\AbstractBaseServiceInterface;
use OrderItem\Data\OrderItemEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<OrderItemEntity>
 */
interface OrderItemServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function create(
        int $order_id,
        int $merchant_id,
        int $product_id,
        int $qty,
        float $total_line_amount,
        int $settled,
        float $percentage_to_platform
    );

    /**
     * @param int $order_item_id
     * @return bool
     */
    public function settle(int $order_item_id);

    /**
     * @param int $order_id
     * @return QueryObject
     */
    public function fetchForOrder(int $order_id);

    /**
     * @param int $merchant_id
     * @return QueryObject
     */
    public function fetchForMerchant(int $merchant_id, int $settled = 0, int $product_id = 0);

}
