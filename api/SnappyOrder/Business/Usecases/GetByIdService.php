<?php
namespace SnappyOrder\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class GetByIdService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;

    public function __construct(SnappyOrderRepositoryInterface $snappyOrderRepository)
    {
        $this->snappyOrderRepository = $snappyOrderRepository;
    }

    public function query(int $id)
    {
        Contracts::requires($id > 0, 'Order ID is required');

        $order = $this->snappyOrderRepository->find($id);
        Contracts::requireEntityFound($order, 'Order');

        return $order;
    }
}
