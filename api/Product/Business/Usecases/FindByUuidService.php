<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class FindByUuidService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function query(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'UUID');

        $product = $this->productRepository->query(['uuid' => $uuid])->fetchOne();
        Contracts::requireEntityFound($product, 'Product');

        return $product;
    }
}
