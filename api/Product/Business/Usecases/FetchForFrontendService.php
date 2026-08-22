<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;

class FetchForFrontendService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function query(array $filters = [])
    {
        $filters['active'] = 1;
        return $this->productRepository->query($filters);
    }
}
