<?php

namespace Thread\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Thread\Business\Dtos\CreateThreadDto;
use Thread\Business\Usecases\CreateThreadService;
use Thread\Business\Usecases\GetThreadListForOrderService;
use Thread\Business\Usecases\MigrateService;

class ThreadController
{
    private MigrateService $migrateService;
    private CreateThreadService $createThreadService;
    private GetThreadListForOrderService $getThreadListForOrderService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        CreateThreadService $createThreadService,
        GetThreadListForOrderService $getThreadListForOrderService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->createThreadService = $createThreadService;
        $this->getThreadListForOrderService = $getThreadListForOrderService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function createThread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $thread = $this->createThreadService->execute(new CreateThreadDto(
            (int) $this->request->get('order_id'),
            (int) $user->id,
            (string) $this->request->get('message'),
            (array) $this->request->get('attachment_url', [])
        ));
        $this->jsonResponseService->success([
            'thread' => $thread,
            'message' => 'Thread created successfully',
        ]);
    }

    function getThreadListForOrder()
    {
        $query = $this->getThreadListForOrderService->query(
            (int) $this->request->get('order_id'),
            $this->request->all()
        );
        $this->jsonResponseService->success([
            'threads' => $query->fetchAll(),
            'count' => $query->count(),
            'message' => 'Threads fetched successfully',
        ]);
    }
}
