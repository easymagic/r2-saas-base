<?php
namespace ProxyOrderChangeLog\Business\Usecases;

use ProxyOrderChangeLog\Data\ProxyOrderChangeLogMigrationRepositoryInterface;

class MigrateService
{
    private ProxyOrderChangeLogMigrationRepositoryInterface $proxyOrderChangeLogMigrationRepository;

    public function __construct(
        ProxyOrderChangeLogMigrationRepositoryInterface $proxyOrderChangeLogMigrationRepository
    ) {
        $this->proxyOrderChangeLogMigrationRepository = $proxyOrderChangeLogMigrationRepository;
    }

    public function execute()
    {
        return $this->proxyOrderChangeLogMigrationRepository->migrate();
    }
}
