<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class FetchForMerchantService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function query(int $merchant_id, array $filters = [])
    {
        Contracts::requires($merchant_id > 0, 'Merchant ID is required');

        $filters['user_id'] = $merchant_id;
        return $this->productRepository->query($filters);
    }
}
