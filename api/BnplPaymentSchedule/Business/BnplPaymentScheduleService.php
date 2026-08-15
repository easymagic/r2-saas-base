<?php

namespace BnplPaymentSchedule\Business;

use Shared\AbstractBaseService;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;
use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<BnplPaymentScheduleEntity, BnplPaymentScheduleRepositoryInterface>
 */
class BnplPaymentScheduleService extends AbstractBaseService implements BnplPaymentScheduleServiceInterface
{
    private BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepositoryInterface;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;

    public function __construct(
        BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepositoryInterface,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository
    ) {
        parent::__construct($bnplPaymentScheduleRepository);
        $this->bnplPaymentScheduleMigrationRepositoryInterface = $bnplPaymentScheduleMigrationRepositoryInterface;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
    }

    public function migrate()
    {
        return $this->bnplPaymentScheduleMigrationRepositoryInterface->migrate();
    }
}
