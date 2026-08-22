<?php
namespace Cart\Business\Usecases;

use Cart\Data\CartRepositoryInterface;
use Shared\Contracts;

class GetCartTotalService
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
        $query = $this->cartRepository->query(['uuid' => $uuid]);
        return $query->sum('price_total');
    }
}
