<?php
namespace Batch\Business\Usecases;

use Batch\Data\BatchMigrationRepositoryInterface;

class MigrateService
{
    private BatchMigrationRepositoryInterface $batchMigrationRepository;

    public function __construct(BatchMigrationRepositoryInterface $batchMigrationRepository)
    {
        $this->batchMigrationRepository = $batchMigrationRepository;
    }

    public function execute()
    {
        return $this->batchMigrationRepository->migrate();
    }
}
