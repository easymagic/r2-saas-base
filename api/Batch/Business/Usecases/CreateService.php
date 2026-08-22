<?php
namespace Batch\Business\Usecases;

use Batch\Business\Dtos\CreateDto;
use Batch\Data\BatchEntity;
use Batch\Data\BatchRepositoryInterface;

class CreateService
{
    private BatchRepositoryInterface $batchRepository;

    public function __construct(BatchRepositoryInterface $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    public function execute(CreateDto $createDto)
    {
        return $this->batchRepository->save(new BatchEntity([
            'name' => $createDto->name,
            'description' => $createDto->description,
        ]));
    }
}
