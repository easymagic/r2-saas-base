<?php
namespace Cart\Business\Usecases;

use Cart\Data\CartMigrationRepositoryInterface;

class MigrateService
{
    private CartMigrationRepositoryInterface $cartMigrationRepository;

    public function __construct(CartMigrationRepositoryInterface $cartMigrationRepository)
    {
        $this->cartMigrationRepository = $cartMigrationRepository;
    }

    public function execute()
    {
        return $this->cartMigrationRepository->migrate();
    }
}
