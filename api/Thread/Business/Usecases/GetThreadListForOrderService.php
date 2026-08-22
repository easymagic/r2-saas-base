<?php
namespace Thread\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use Thread\Data\ThreadRepositoryInterface;

class GetThreadListForOrderService
{
    private ThreadRepositoryInterface $threadRepository;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;

    public function __construct(
        ThreadRepositoryInterface $threadRepository,
        SnappyOrderRepositoryInterface $snappyOrderRepository
    ) {
        $this->threadRepository = $threadRepository;
        $this->snappyOrderRepository = $snappyOrderRepository;
    }

    public function query(int $order_id, array $filters = [])
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->snappyOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $filters['order_id'] = $order_id;
        return $this->threadRepository->query($filters);
    }
}
