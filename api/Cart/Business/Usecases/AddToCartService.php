<?php
namespace Cart\Business\Usecases;

use Cart\Business\Dtos\AddToCartDto;
use Cart\Data\CartEntity;
use Cart\Data\CartRepositoryInterface;
use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class AddToCartService
{
    private CartRepositoryInterface $cartRepository;
    private ProductRepositoryInterface $productRepository;
    private CartSupport $cartSupport;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        CartSupport $cartSupport
    ) {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->cartSupport = $cartSupport;
    }

    public function execute(AddToCartDto $addToCartDto)
    {
        $product = $this->productRepository->find($addToCartDto->productId);
        Contracts::requireEntityFound($product, 'Product');
        Contracts::requires((int) $product->active === 1, 'Product is not available');

        $existing = $this->cartSupport->findCartLine($addToCartDto->uuid, $addToCartDto->productId);
        $newQty = $existing !== null ? ((int) $existing->qty + $addToCartDto->qty) : $addToCartDto->qty;

        Contracts::requires($newQty <= (int) $product->stock_qty, 'Insufficient stock');

        $priceTotal = (float) $product->price * $newQty;

        if ($existing !== null) {
            $existing->qty = $newQty;
            $existing->price_total = $priceTotal;
            return $this->cartRepository->save($existing);
        }

        return $this->cartRepository->save(new CartEntity([
            'cart_sess_uuid' => $addToCartDto->uuid,
            'product_id' => $addToCartDto->productId,
            'merchant_id' => (int) $product->user_id,
            'qty' => $newQty,
            'price_total' => $priceTotal,
        ]));
    }
}
