<?php
namespace Cart\Business\Usecases;

use Cart\Data\CartRepositoryInterface;
use Shared\Contracts;

class GetCartService
{
    private CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function query(string $uuid)
    {
        $uuid = trim($uuid);
        Contracts::requiresNotNullOrEmpty($uuid, 'cart session uuid');

        return $this->cartRepository->query(['uuid' => $uuid])->fetchAll();
    }
}
