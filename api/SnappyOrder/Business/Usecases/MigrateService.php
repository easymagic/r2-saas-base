<?php
namespace SnappyOrder\Business\Usecases;

use SnappyOrder\Data\SnappyOrderMigrationRepositoryInterface;

class MigrateService
{
    private SnappyOrderMigrationRepositoryInterface $snappyOrderMigrationRepository;

    public function __construct(SnappyOrderMigrationRepositoryInterface $snappyOrderMigrationRepository)
    {
        $this->snappyOrderMigrationRepository = $snappyOrderMigrationRepository;
    }

    public function execute()
    {
        return $this->snappyOrderMigrationRepository->migrate();
    }
}
