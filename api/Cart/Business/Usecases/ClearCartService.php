<?php
namespace Cart\Business\Usecases;

use Cart\Data\CartRepositoryInterface;
use Shared\Contracts;

class ClearCartService
{
    private CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function execute(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        $items = $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
        foreach ($items as $item) {
            $this->cartRepository->delete((int) $item->id);
        }
    }
}
