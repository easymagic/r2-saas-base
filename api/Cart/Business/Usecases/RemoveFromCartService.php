<?php
namespace Cart\Business\Usecases;

use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Data\CartRepositoryInterface;
use Shared\Contracts;

class RemoveFromCartService
{
    private CartRepositoryInterface $cartRepository;
    private CartSupport $cartSupport;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartSupport $cartSupport
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartSupport = $cartSupport;
    }

    public function execute(RemoveFromCartDto $removeFromCartDto)
    {
        $existing = $this->cartSupport->findCartLine(
            $removeFromCartDto->uuid,
            $removeFromCartDto->productId
        );
        Contracts::requires($existing !== null, 'Cart item not found');

        $this->cartRepository->delete((int) $existing->id);
    }
}
