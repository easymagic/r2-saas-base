<?php 
namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Business\Usecases\GetAllSchedulesForOrder;
use EcomOrder\Data\EcomOrderEntity;
use OrderItem\Business\Usecases\FetchForOrderService;

class AnnotateOrdersWithItemsService
{
    private FetchForOrderService $fetchForOrderService;
    private GetAllSchedulesForOrder $getAllSchedulesForOrderService;

    public function __construct(FetchForOrderService $fetchForOrderService, GetAllSchedulesForOrder $getAllSchedulesForOrderService)
    {
        $this->fetchForOrderService = $fetchForOrderService;
        $this->getAllSchedulesForOrderService = $getAllSchedulesForOrderService;
    }

    /**
     * @param array<EcomOrderEntity> $orders
     */
    public function execute(array $orders){
        foreach ($orders as $order) {
            $orderItems = $this->fetchForOrderService->query($order->id);
            $order->items = $orderItems->fetchAll();
            $this->applySchedules($order->type === 'bnpl', $order);
        }
        return $orders;
    }

    private function applySchedules(bool $condition, EcomOrderEntity $order){
        if ($condition) {
            $schedules = $this->getAllSchedulesForOrderService->execute($order->id);
            $order->schedules = $schedules->fetch();
        }
    }
}