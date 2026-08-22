<?php

namespace Cart\Business;

use Shared\Contracts;
use Shared\AbstractBaseService;
use Cart\Business\Dtos\AddToCartDto;
use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Data\CartRepositoryInterface;
use Cart\Data\CartEntity;
use Cart\Data\CartMigrationRepositoryInterface;
use Product\Data\ProductRepositoryInterface;

/**
 * @extends AbstractBaseService<CartEntity, CartRepositoryInterface>
 */
class CartService extends AbstractBaseService implements CartServiceInterface
{
    private CartMigrationRepositoryInterface $cartMigrationRepositoryInterface;
    private CartRepositoryInterface $cartRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        CartMigrationRepositoryInterface $cartMigrationRepositoryInterface,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($cartRepository);
        $this->cartMigrationRepositoryInterface = $cartMigrationRepositoryInterface;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function migrate()
    {
        return $this->cartMigrationRepositoryInterface->migrate();
    }

    public function getCartTotal(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
        $query = $this->cartRepository->query(['uuid' => $uuid]);
        return $query->sum('price_total');
    }

    public function addToCart(AddToCartDto $addToCartDto)
    {
        $product = $this->productRepository->find($addToCartDto->productId);
        Contracts::requireEntityFound($product, 'Product');
        Contracts::requires((int) $product->active === 1, 'Product is not available');

        $existing = $this->findCartLine($addToCartDto->uuid, $addToCartDto->productId);
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

    public function getCart(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        return $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
    }

    public function removeFromCart(RemoveFromCartDto $removeFromCartDto)
    {
        $existing = $this->findCartLine($removeFromCartDto->uuid, $removeFromCartDto->productId);
        Contracts::requires($existing !== null, 'Cart item not found');

        $this->cartRepository->delete((int) $existing->id);
    }

    public function clearCart(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        $items = $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
        foreach ($items as $item) {
            $this->cartRepository->delete((int) $item->id);
        }
    }

    public function generateCartUuid()
    {
        return uniqid('cart_' . time() . '_' . date('Y-m-d-H-i-s'), true);
    }

    /**
     * @param string $uuid
     * @param int $productId
     * @return CartEntity|null
     */
    private function findCartLine(string $uuid, int $productId)
    {
        $items = $this->cartRepository->query([
            'uuid' => $uuid,
            'product_id' => $productId,
        ])->fetchAll();

        if (empty($items)) {
            return null;
        }

        return $items[0];
    }
}
