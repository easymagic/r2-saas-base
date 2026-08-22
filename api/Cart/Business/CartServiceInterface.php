<?php

namespace Cart\Business;

use Shared\AbstractBaseServiceInterface;
use Cart\Business\Dtos\AddToCartDto;
use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Data\CartEntity;

/**
 * @extends AbstractBaseServiceInterface<CartEntity>
 */
interface CartServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param AddToCartDto $addToCartDto
     * @return CartEntity
     */
    public function addToCart(AddToCartDto $addToCartDto);

    /**
     * @param string $uuid
     * @return CartEntity[]
     */
    public function getCart(string $uuid);

    /**
     * @param RemoveFromCartDto $removeFromCartDto
     * @return void
     */
    public function removeFromCart(RemoveFromCartDto $removeFromCartDto);

    public function clearCart(string $uuid);

    /**
     * @param string $uuid
     * @return float
     */
    public function getCartTotal(string $uuid);

    /**
     * @return string
     */
    public function generateCartUuid();
}
