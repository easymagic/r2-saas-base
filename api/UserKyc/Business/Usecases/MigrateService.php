<?php
namespace UserKyc\Business\Usecases;

use UserKyc\Data\UserKycMigrationRepositoryInterface;

class MigrateService
{
    private UserKycMigrationRepositoryInterface $userKycMigrationRepository;

    public function __construct(UserKycMigrationRepositoryInterface $userKycMigrationRepository)
    {
        $this->userKycMigrationRepository = $userKycMigrationRepository;
    }

    public function execute()
    {
        return $this->userKycMigrationRepository->migrate();
    }
}
