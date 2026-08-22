<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderMigrationRepositoryInterface;

class MigrateService
{
    private EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepository;

    public function __construct(EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepository)
    {
        $this->ecomOrderMigrationRepository = $ecomOrderMigrationRepository;
    }

    public function execute()
    {
        return $this->ecomOrderMigrationRepository->migrate();
    }
}
