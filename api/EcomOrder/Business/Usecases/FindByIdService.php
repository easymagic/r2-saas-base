<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;

class FindByIdService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;

    public function __construct(EcomOrderRepositoryInterface $ecomOrderRepository)
    {
        $this->ecomOrderRepository = $ecomOrderRepository;
    }

    public function query(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        return $order;
    }
}
