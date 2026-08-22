<?php

namespace Batch\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Batch\Business\Dtos\CreateDto;
use Batch\Business\BatchServiceInterface;

class BatchController
{
    private BatchServiceInterface $batchService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        BatchServiceInterface $batchService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->batchService = $batchService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->batchService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $batch = $this->batchService->create(new CreateDto(
            (string) $this->request->get('name'),
            (string) $this->request->get('description')
        ));
        $this->jsonResponseService->success([
            'batch' => $batch,
            'message' => 'Batch created successfully',
        ]);
    }

    function getBatchList()
    {
        $query = $this->batchService->getBatchList($this->request->all());
        $this->jsonResponseService->success([
            'batches' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Batches fetched successfully',
        ]);
    }

    function remove()
    {
        $this->batchService->remove((int) $this->request->get('batch_id'));
        $this->jsonResponseService->success([
            'message' => 'Batch removed successfully',
        ]);
    }
}
