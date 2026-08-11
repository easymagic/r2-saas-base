<?php

namespace UserKyc\Business;

use Shared\AbstractBaseService;
use UserKyc\Data\UserKycRepositoryInterface;
use UserKyc\Data\UserKycEntity;
use UserKyc\Data\UserKycMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<UserKycEntity, UserKycRepositoryInterface>
 */
class UserKycService extends AbstractBaseService implements UserKycServiceInterface
{
    private UserKycMigrationRepositoryInterface $userKycMigrationRepositoryInterface;
    private UserKycRepositoryInterface $userKycRepository;

    public function __construct(
        UserKycMigrationRepositoryInterface $userKycMigrationRepositoryInterface,
        UserKycRepositoryInterface $userKycRepository
    ) {
        parent::__construct($userKycRepository);
        $this->userKycMigrationRepositoryInterface = $userKycMigrationRepositoryInterface;
        $this->userKycRepository = $userKycRepository;
    }

    public function migrate()
    {
        return $this->userKycMigrationRepositoryInterface->migrate();
    }
}
