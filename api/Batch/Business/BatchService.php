<?php

namespace Batch\Business;

use Exception;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
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

    public function create(string $name, string $description)
    {
        if (empty($name)) {
            throw new Exception('Name is required');
        }
        if (empty($description)) {
            throw new Exception('Description is required');
        }

        return $this->batchRepository->save(0, [
            'name' => $name,
            'description' => $description,
        ]);
    }

    /**
     * @param array $filters
     * @return QueryObject
     */
    public function getBatchList(array $filters = [])
    {
        return $this->batchRepository->query($filters);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id)
    {
        if (empty($id)) {
            throw new Exception('Batch ID is required');
        }

        $batch = $this->batchRepository->find($id);
        if ($batch->isEmpty()) {
            throw new Exception('Batch not found');
        }

        $this->batchRepository->delete($id);
        return true;
    }
}
