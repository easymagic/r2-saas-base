<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;

class FetchForAdminService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function query(array $filters = [])
    {
        return $this->productRepository->query($filters);
    }
}
