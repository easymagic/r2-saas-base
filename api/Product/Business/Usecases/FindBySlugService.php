<?php
namespace Product\Business\Usecases;

use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class FindBySlugService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function query(string $slug)
    {
        $slug = trim($slug);
        Contracts::requiresNotNullOrEmpty($slug, 'Slug');

        $product = $this->productRepository->query(['slug' => $slug])->fetchOne();
        Contracts::requireEntityFound($product, 'Product');

        return $product;
    }
}
