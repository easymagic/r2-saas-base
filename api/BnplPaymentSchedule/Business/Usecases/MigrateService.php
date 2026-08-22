<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepositoryInterface;

class MigrateService
{
    private BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepository;

    public function __construct(
        BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepository
    ) {
        $this->bnplPaymentScheduleMigrationRepository = $bnplPaymentScheduleMigrationRepository;
    }

    public function execute()
    {
        return $this->bnplPaymentScheduleMigrationRepository->migrate();
    }
}
