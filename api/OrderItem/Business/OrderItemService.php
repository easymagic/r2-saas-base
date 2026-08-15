<?php

namespace OrderItem\Business;

use Shared\AbstractBaseService;
use OrderItem\Data\OrderItemRepositoryInterface;
use OrderItem\Data\OrderItemEntity;
use OrderItem\Data\OrderItemMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<OrderItemEntity, OrderItemRepositoryInterface>
 */
class OrderItemService extends AbstractBaseService implements OrderItemServiceInterface
{
    private OrderItemMigrationRepositoryInterface $orderItemMigrationRepositoryInterface;
    private OrderItemRepositoryInterface $orderItemRepository;

    public function __construct(
        OrderItemMigrationRepositoryInterface $orderItemMigrationRepositoryInterface,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($orderItemRepository);
        $this->orderItemMigrationRepositoryInterface = $orderItemMigrationRepositoryInterface;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function migrate()
    {
        return $this->orderItemMigrationRepositoryInterface->migrate();
    }
}
