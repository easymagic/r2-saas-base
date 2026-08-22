<?php
namespace Batch\Business\Usecases;

use Batch\Data\BatchRepositoryInterface;
use Shared\Contracts;

class RemoveService
{
    private BatchRepositoryInterface $batchRepository;

    public function __construct(BatchRepositoryInterface $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function execute(int $id)
    {
        Contracts::requires($id > 0, 'Batch ID is required');
        $batch = $this->batchRepository->find($id);
        Contracts::requireEntityFound($batch, 'Batch');
        $this->batchRepository->delete($id);
        return true;
    }
}
