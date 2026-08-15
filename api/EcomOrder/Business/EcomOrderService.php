<?php

namespace EcomOrder\Business;

use Shared\AbstractBaseService;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<EcomOrderEntity, EcomOrderRepositoryInterface>
 */
class EcomOrderService extends AbstractBaseService implements EcomOrderServiceInterface
{
    private EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepositoryInterface;
    private EcomOrderRepositoryInterface $ecomOrderRepository;

    public function __construct(
        EcomOrderMigrationRepositoryInterface $ecomOrderMigrationRepositoryInterface,
        EcomOrderRepositoryInterface $ecomOrderRepository
    ) {
        parent::__construct($ecomOrderRepository);
        $this->ecomOrderMigrationRepositoryInterface = $ecomOrderMigrationRepositoryInterface;
        $this->ecomOrderRepository = $ecomOrderRepository;
    }

    public function migrate()
    {
        return $this->ecomOrderMigrationRepositoryInterface->migrate();
    }
}
