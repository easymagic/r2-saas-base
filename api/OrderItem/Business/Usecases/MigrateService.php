<?php
namespace OrderItem\Business\Usecases;

use OrderItem\Data\OrderItemMigrationRepositoryInterface;

class MigrateService
{
    private OrderItemMigrationRepositoryInterface $orderItemMigrationRepository;

    public function __construct(OrderItemMigrationRepositoryInterface $orderItemMigrationRepository)
    {
        $this->orderItemMigrationRepository = $orderItemMigrationRepository;
    }

    public function execute()
    {
        return $this->orderItemMigrationRepository->migrate();
    }
}
