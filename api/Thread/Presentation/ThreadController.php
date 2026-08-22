<?php

namespace Thread\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Thread\Business\Dtos\CreateThreadDto;
use Thread\Business\ThreadServiceInterface;

class ThreadController
{
    private ThreadServiceInterface $threadService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        ThreadServiceInterface $threadService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->threadService = $threadService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->threadService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function createThread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $thread = $this->threadService->createThread(new CreateThreadDto(
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
        $query = $this->threadService->getThreadListForOrder(
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
