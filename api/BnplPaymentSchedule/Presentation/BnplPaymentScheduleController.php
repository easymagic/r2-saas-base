<?php

namespace BnplPaymentSchedule\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use BnplPaymentSchedule\Business\BnplPaymentScheduleServiceInterface;

class BnplPaymentScheduleController
{
    private BnplPaymentScheduleServiceInterface $bnplPaymentScheduleService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        BnplPaymentScheduleServiceInterface $bnplPaymentScheduleService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->bnplPaymentScheduleService = $bnplPaymentScheduleService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->bnplPaymentScheduleService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }
}
