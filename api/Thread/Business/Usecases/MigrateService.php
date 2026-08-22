<?php
namespace Thread\Business\Usecases;

use Thread\Data\ThreadMigrationRepositoryInterface;

class MigrateService
{
    private ThreadMigrationRepositoryInterface $threadMigrationRepository;

    public function __construct(ThreadMigrationRepositoryInterface $threadMigrationRepository)
    {
        $this->threadMigrationRepository = $threadMigrationRepository;
    }

    public function execute()
    {
        return $this->threadMigrationRepository->migrate();
    }
}
