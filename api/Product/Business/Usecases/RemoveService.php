<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class RemoveService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(int $id)
    {
        Contracts::requires($id > 0, 'Product ID is required');

        $product = $this->productRepository->find($id);
        Contracts::requireEntityFound($product, 'Product');

        $this->productRepository->delete($id);
        return true;
    }
}
