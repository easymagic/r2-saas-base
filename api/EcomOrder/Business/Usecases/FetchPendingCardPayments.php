<?php 
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepository;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Query\QueryObject;
use EcomOrder\Data\EcomOrderEntity;

class FetchPendingCardPayments
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;

    public function __construct(EcomOrderRepositoryInterface $ecomOrderRepository)
    {
        $this->ecomOrderRepository = $ecomOrderRepository;
    }

    /**
     * @return QueryObject<EcomOrderEntity>
     */
    public function query()
    {
        return $this->ecomOrderRepository->query([
            'pending_payments' => true,
            'card_payments' => true
        ]);
    }
}