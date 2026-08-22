<?php

namespace Cart\Presentation;

use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Cart\Business\Dtos\AddToCartDto;
use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Business\CartServiceInterface;

class CartController
{
    private CartServiceInterface $cartService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        CartServiceInterface $cartService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->cartService = $cartService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->cartService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function addToCart()
    {
        $item = $this->cartService->addToCart(new AddToCartDto(
            (string) $this->request->get('uuid', ''),
            (int) $this->request->get('product_id', 0),
            (int) $this->request->get('qty', 0)
        ));
        $this->jsonResponseService->success([
            'cart_item' => $item,
            'message' => 'Item added to cart successfully',
        ]);
    }

    function getCart()
    {
        $items = $this->cartService->getCart((string) $this->request->get('uuid', ''));
        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) $item->price_total;
        }
        $this->jsonResponseService->success([
            'cart' => $items,
            'count' => count($items),
            'price_total' => $total,
            'message' => 'Cart fetched successfully',
        ]);
    }

    function removeFromCart()
    {
        $this->cartService->removeFromCart(new RemoveFromCartDto(
            (string) $this->request->get('uuid', ''),
            (int) $this->request->get('product_id', 0)
        ));
        $this->jsonResponseService->success([
            'message' => 'Item removed from cart successfully',
        ]);
    }

    function clearCart()
    {
        $this->cartService->clearCart((string) $this->request->get('uuid', ''));
        $this->jsonResponseService->success([
            'message' => 'Cart cleared successfully',
        ]);
    }

    function generateCartUuid()
    {
        $uuid = $this->cartService->generateCartUuid();
        $this->jsonResponseService->success([
            'uuid' => $uuid,
            'message' => 'Cart UUID generated successfully',
        ]);
    }
}
