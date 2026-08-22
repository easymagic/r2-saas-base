<?php
namespace PlatformConfig\Business\Usecases;

use PlatformConfig\Data\PlatformConfigMigrationRepositoryInterface;

class MigrateService
{
    private PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository;

    public function __construct(
        PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository
    ) {
        $this->platformConfigMigrationRepository = $platformConfigMigrationRepository;
    }

    public function execute()
    {
        return $this->platformConfigMigrationRepository->migrate();
    }
}
