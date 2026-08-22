<?php

namespace OrderItem\Business;

use Shared\AbstractBaseServiceInterface;
use OrderItem\Business\Dtos\CreateDto;
use OrderItem\Business\Dtos\FetchForMerchantDto;
use OrderItem\Data\OrderItemEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<OrderItemEntity>
 */
interface OrderItemServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param CreateDto $createDto
     * @return OrderItemEntity
     */
    public function create(CreateDto $createDto);

    public function settle(int $order_item_id);

    /**
     * @param int $order_id
     * @return QueryObject<OrderItemEntity>
     */
    public function fetchForOrder(int $order_id);

    /**
     * @param FetchForMerchantDto $fetchForMerchantDto
     * @return QueryObject
     */
    public function fetchForMerchant(FetchForMerchantDto $fetchForMerchantDto);
}
