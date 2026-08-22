<?php 
namespace EcomOrder\Business\Usecases;

use Shared\Contracts;

class TrackOrderService
{
    private FindByIdService $findByIdService;

    const STAGES = [
        'pending',
        'picked-up',
        'on-the-way',
        'delivered'
    ];

    public function __construct(
        FindByIdService $findByIdService
    ) {
        $this->findByIdService = $findByIdService;
    }

    function query(int $order_id, string $reference){
        $order = $this->findByIdService->query($order_id);
        Contracts::requires($order->reference === $reference, 'Reference does not match');
        return [
            "order" => $order,
            "stages" => self::STAGES,
            "current_stage" => $order->delivery_status,
        ];

    }
}