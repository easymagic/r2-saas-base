<?php

namespace Batch\Business;

use Exception;
use Shared\AbstractBaseService;
use Shared\Contracts;
use Shared\Query\QueryObject;
use Batch\Business\Dtos\CreateDto;
use Batch\Data\BatchRepositoryInterface;
use Batch\Data\BatchEntity;
use Batch\Data\BatchMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<BatchEntity, BatchRepositoryInterface>
 */
class BatchService extends AbstractBaseService implements BatchServiceInterface
{
    private BatchMigrationRepositoryInterface $batchMigrationRepositoryInterface;
    private BatchRepositoryInterface $batchRepository;

    public function __construct(
        BatchMigrationRepositoryInterface $batchMigrationRepositoryInterface,
        BatchRepositoryInterface $batchRepository
    ) {
        parent::__construct($batchRepository);
        $this->batchMigrationRepositoryInterface = $batchMigrationRepositoryInterface;
        $this->batchRepository = $batchRepository;
    }

    public function migrate()
    {
        return $this->batchMigrationRepositoryInterface->migrate();
    }

    public function create(CreateDto $createDto)
    {
        return $this->batchRepository->save(new BatchEntity([
            'name' => $createDto->name,
            'description' => $createDto->description,
        ]));
    }

    public function getBatchList(array $filters = [])
    {
        return $this->batchRepository->query($filters);
    }

    public function remove(int $id)
    {
        Contracts::requires($id > 0, 'Batch ID is required');
        $batch = $this->batchRepository->find($id);
        Contracts::requireEntityFound($batch, 'Batch');
        $this->batchRepository->delete($id);
        return true;
    }
}
