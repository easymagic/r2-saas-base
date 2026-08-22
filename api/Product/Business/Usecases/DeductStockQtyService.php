<?php
namespace Product\Business\Usecases;

use Product\Business\Dtos\DeductStockQtyDto;
use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class DeductStockQtyService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(DeductStockQtyDto $deductStockQtyDto)
    {
        $product = $this->productRepository->find($deductStockQtyDto->id);
        Contracts::requireEntityFound($product, 'Product');

        $product->stock_qty -= $deductStockQtyDto->qty;
        return $this->productRepository->save($product);
    }
}
