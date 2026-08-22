<?php
namespace Cart\Business\Usecases;

use Cart\Data\CartRepositoryInterface;

class CartSupport
{
    private CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param string $uuid
     * @param int $productId
     * @return \Cart\Data\CartEntity|null
     */
    public function findCartLine(string $uuid, int $productId)
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
