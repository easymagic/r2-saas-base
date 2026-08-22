<?php
namespace Product\Business\Usecases;

use Product\Data\ProductMigrationRepositoryInterface;

class MigrateService
{
    private ProductMigrationRepositoryInterface $productMigrationRepository;

    public function __construct(ProductMigrationRepositoryInterface $productMigrationRepository)
    {
        $this->productMigrationRepository = $productMigrationRepository;
    }

    public function execute()
    {
        return $this->productMigrationRepository->migrate();
    }
}
