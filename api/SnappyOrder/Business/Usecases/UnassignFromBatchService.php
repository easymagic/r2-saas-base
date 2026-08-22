<?php
namespace SnappyOrder\Business\Usecases;

use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\Usecases\LogService as ProxyOrderChangeLogService;
use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class UnassignFromBatchService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private ProxyOrderChangeLogService $proxyOrderChangeLogService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        ProxyOrderChangeLogService $proxyOrderChangeLogService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
    }

    public function execute(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');

        $order = $this->snappyOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires(!empty($order->batch_id), 'Order is not assigned to a batch');

        $old_batch_id = (string) $order->batch_id;
        $order->batch_id = 0;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->execute(new ProxyOrderChangeLogDto(
            $order->id,
            'batch_id',
            $old_batch_id,
            ''
        ));

        return $order;
    }
}
