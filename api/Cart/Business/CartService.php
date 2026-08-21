<?php

namespace Cart\Business;

use App\Shared\Contracts\Contracts;
use Shared\AbstractBaseService;
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

    /**
     * @param string $uuid
     * @param int $productId
     * @param int $qty
     * @return CartEntity
     */
    public function addToCart(string $uuid, int $productId, int $qty)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
        Contracts::requires($productId > 0, 'Product ID is required');
        Contracts::requires($qty > 0, 'Quantity must be greater than 0');

        $product = $this->productRepository->find($productId);
        Contracts::requireEntityFound($product, 'Product');
        Contracts::requires((int) $product->active === 1, 'Product is not available');

        $existing = $this->findCartLine($uuid, $productId);
        $newQty = $existing !== null ? ((int) $existing->qty + $qty) : $qty;

        Contracts::requires($newQty <= (int) $product->stock_qty, 'Insufficient stock');

        $priceTotal = (float) $product->price * $newQty;

        if ($existing !== null) {
            return $this->cartRepository->save((int) $existing->id, [
                'qty' => $newQty,
                'price_total' => $priceTotal,
            ]);
        }

        return $this->cartRepository->save(0, [
            'cart_sess_uuid' => $uuid,
            'product_id' => $productId,
            'merchant_id' => (int) $product->user_id,
            'qty' => $newQty,
            'price_total' => $priceTotal,
        ]);
    }

    /**
     * @param string $uuid
     * @return CartEntity[]
     */
    public function getCart(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        return $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
    }

    /**
     * @param string $uuid
     * @param int $productId
     * @return void
     */
    public function removeFromCart(string $uuid, int $productId)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');
        Contracts::requires($productId > 0, 'Product ID is required');

        $existing = $this->findCartLine($uuid, $productId);
        Contracts::requires($existing !== null, 'Cart item not found');

        $this->cartRepository->delete((int) $existing->id);
    }

    /**
     * @param string $uuid
     * @return void
     */
    public function clearCart(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        $items = $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
        foreach ($items as $item) {
            $this->cartRepository->delete((int) $item->id);
        }
    }

    /**
     * Generate a new cart UUID
     * @return string
     */
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
