<?php
namespace SnappyOrder\Business\Usecases;

use Batch\Data\BatchRepositoryInterface;
use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\Usecases\LogService as ProxyOrderChangeLogService;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\AssignToBatchDto;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class AssignToBatchService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private BatchRepositoryInterface $batchRepository;
    private ProxyOrderChangeLogService $proxyOrderChangeLogService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        BatchRepositoryInterface $batchRepository,
        ProxyOrderChangeLogService $proxyOrderChangeLogService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->batchRepository = $batchRepository;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
    }

    public function execute(AssignToBatchDto $assignToBatchDto)
    {
        $batch = $this->batchRepository->find($assignToBatchDto->batch_id);
        Contracts::requireEntityFound($batch, 'Batch');

        $order = $this->snappyOrderRepository->find($assignToBatchDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $old_batch_id = (string) $order->batch_id;
        $order->batch_id = $assignToBatchDto->batch_id;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->execute(new ProxyOrderChangeLogDto(
            $order->id,
            'batch_id',
            $old_batch_id,
            (string) $assignToBatchDto->batch_id
        ));

        return $order;
    }
}
