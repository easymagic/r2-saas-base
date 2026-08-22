<?php

namespace BnplPaymentSchedule\Presentation;

use BnplPaymentSchedule\Business\Usecases\ChargeScheduleService;
use BnplPaymentSchedule\Business\Usecases\GetFirstScheduleService;
use BnplPaymentSchedule\Business\Usecases\GetNextScheduleService;
use BnplPaymentSchedule\Business\Usecases\MigrateService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class BnplPaymentScheduleController
{
    private MigrateService $migrateService;
    private GetFirstScheduleService $getFirstScheduleService;
    private GetNextScheduleService $getNextScheduleService;
    private ChargeScheduleService $chargeScheduleService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        MigrateService $migrateService,
        GetFirstScheduleService $getFirstScheduleService,
        GetNextScheduleService $getNextScheduleService,
        ChargeScheduleService $chargeScheduleService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->migrateService = $migrateService;
        $this->getFirstScheduleService = $getFirstScheduleService;
        $this->getNextScheduleService = $getNextScheduleService;
        $this->chargeScheduleService = $chargeScheduleService;
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

    function getFirstSchedule()
    {
        $schedule = $this->getFirstScheduleService->query((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'schedule' => $schedule,
            'message' => 'First BNPL schedule fetched successfully',
        ]);
    }

    function getNextSchedule()
    {
        $schedule = $this->getNextScheduleService->query((int) $this->request->get('order_id'));
        $this->jsonResponseService->success([
            'schedule' => $schedule,
            'message' => 'Next BNPL schedule fetched successfully',
        ]);
    }

    function chargeSchedule()
    {
        $result = $this->chargeScheduleService->execute((int) $this->request->get('schedule_id'));
        $this->jsonResponseService->success([
            'result' => $result,
            'message' => $result ? 'Schedule charged successfully' : 'Schedule charge failed',
        ]);
    }
}
