<?php

namespace Cart\Business;

use Shared\AbstractBaseServiceInterface;
use Cart\Data\CartEntity;

/**
 * @extends AbstractBaseServiceInterface<CartEntity>
 */
interface CartServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function addToCart(string $uuid, int $productId, int $qty);

    /**
     * Get the cart items
     * @param string $uuid
     * @return CartEntity[]
     */
    public function getCart(string $uuid);
    public function removeFromCart(string $uuid, int $productId);
    public function clearCart(string $uuid);
    /**
     * Get the total amount of the cart
     * @param string $uuid
     * @return float
     */
    public function getCartTotal(string $uuid);
    /**
     * Generate a new cart UUID
     * @return string
     */
    public function generateCartUuid();
}
