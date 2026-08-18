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

    function getFirstSchedule()
    {
        $schedule = $this->bnplPaymentScheduleService->getFirstSchedule((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'schedule' => $schedule,
            'message' => 'First BNPL schedule fetched successfully',
        ]);
    }

    function getNextSchedule()
    {
        $schedule = $this->bnplPaymentScheduleService->getNextSchedule((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'schedule' => $schedule,
            'message' => 'Next BNPL schedule fetched successfully',
        ]);
    }

    function chargeSchedule()
    {
        $result = $this->bnplPaymentScheduleService->chargeSchedule((int) $this->request->get('schedule_id'));
        $this->jsonResponseService->success([
            'result' => $result,
            'message' => $result ? 'Schedule charged successfully' : 'Schedule charge failed',
        ]);
    }
}
