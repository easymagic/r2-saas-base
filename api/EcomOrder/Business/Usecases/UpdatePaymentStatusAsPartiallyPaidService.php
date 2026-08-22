<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;

class UpdatePaymentStatusAsPartiallyPaidService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderSupport $ecomOrderSupport;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderSupport $ecomOrderSupport
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderSupport = $ecomOrderSupport;
    }

    public function execute(int $order_id)
    {
        $order = $this->ecomOrderSupport->requirePendingPaymentOrder($order_id);
        $order->payment_status = 'part-paid';
        return $this->ecomOrderRepository->save($order);
    }
}
