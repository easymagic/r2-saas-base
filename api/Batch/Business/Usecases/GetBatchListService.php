<?php
namespace Batch\Business\Usecases;

use Batch\Data\BatchRepositoryInterface;

class GetBatchListService
{
    private BatchRepositoryInterface $batchRepository;

    public function __construct(BatchRepositoryInterface $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function query(array $filters = [])
    {
        return $this->batchRepository->query($filters);
    }
}
