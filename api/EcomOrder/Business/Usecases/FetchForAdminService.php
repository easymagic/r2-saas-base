<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;

class FetchForAdminService
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

    public function query(array $filters = [])
    {
        return $this->ecomOrderRepository->query($this->ecomOrderSupport->sanitizeListFilters($filters));
    }
}
