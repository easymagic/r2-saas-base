<?php

namespace Batch\Presentation;

use Batch\Business\Dtos\CreateDto;
use Batch\Business\Usecases\CreateService;
use Batch\Business\Usecases\GetBatchListService;
use Batch\Business\Usecases\MigrateService;
use Batch\Business\Usecases\RemoveService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class BatchController
{
    private MigrateService $migrateService;
    private CreateService $createService;
    private GetBatchListService $getBatchListService;
    private RemoveService $removeService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        MigrateService $migrateService,
        CreateService $createService,
        GetBatchListService $getBatchListService,
        RemoveService $removeService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->migrateService = $migrateService;
        $this->createService = $createService;
        $this->getBatchListService = $getBatchListService;
        $this->removeService = $removeService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $batch = $this->createService->execute(new CreateDto(
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
        $query = $this->getBatchListService->query($this->request->all());
        $this->jsonResponseService->success([
            'batches' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Batches fetched successfully',
        ]);
    }

    function remove()
    {
        $this->removeService->execute((int) $this->request->get('batch_id'));
        $this->jsonResponseService->success([
            'message' => 'Batch removed successfully',
        ]);
    }
}
