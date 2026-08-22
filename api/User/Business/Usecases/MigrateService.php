<?php
namespace User\Business\Usecases;

use User\Data\UserMigrationRepositoryInterface;

class MigrateService
{
    private UserMigrationRepositoryInterface $userMigrationRepository;

    public function __construct(UserMigrationRepositoryInterface $userMigrationRepository)
    {
        $this->userMigrationRepository = $userMigrationRepository;
    }

    public function execute()
    {
        return $this->userMigrationRepository->migrate();
    }
}
