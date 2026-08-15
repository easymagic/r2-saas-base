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
    public function getCart(string $uuid);
    public function removeFromCart(string $uuid, int $productId);
    public function clearCart(string $uuid);
    /**
     * Generate a new cart UUID
     * @return string
     */
    public function generateCartUuid();
}
