<?php

namespace Cart\Presentation;

use Cart\Business\Dtos\AddToCartDto;
use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Business\Usecases\AddToCartService;
use Cart\Business\Usecases\ClearCartService;
use Cart\Business\Usecases\GenerateCartUuidService;
use Cart\Business\Usecases\GetCartService;
use Cart\Business\Usecases\MigrateService;
use Cart\Business\Usecases\RemoveFromCartService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class CartController
{
    private MigrateService $migrateService;
    private AddToCartService $addToCartService;
    private GetCartService $getCartService;
    private RemoveFromCartService $removeFromCartService;
    private ClearCartService $clearCartService;
    private GenerateCartUuidService $generateCartUuidService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        MigrateService $migrateService,
        AddToCartService $addToCartService,
        GetCartService $getCartService,
        RemoveFromCartService $removeFromCartService,
        ClearCartService $clearCartService,
        GenerateCartUuidService $generateCartUuidService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->migrateService = $migrateService;
        $this->addToCartService = $addToCartService;
        $this->getCartService = $getCartService;
        $this->removeFromCartService = $removeFromCartService;
        $this->clearCartService = $clearCartService;
        $this->generateCartUuidService = $generateCartUuidService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function addToCart()
    {
        $item = $this->addToCartService->execute(new AddToCartDto(
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
        $items = $this->getCartService->query((string) $this->request->get('uuid', ''));
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
        $this->removeFromCartService->execute(new RemoveFromCartDto(
            (string) $this->request->get('uuid', ''),
            (int) $this->request->get('product_id', 0)
        ));
        $this->jsonResponseService->success([
            'message' => 'Item removed from cart successfully',
        ]);
    }

    function clearCart()
    {
        $this->clearCartService->execute((string) $this->request->get('uuid', ''));
        $this->jsonResponseService->success([
            'message' => 'Cart cleared successfully',
        ]);
    }

    function generateCartUuid()
    {
        $uuid = $this->generateCartUuidService->execute();
        $this->jsonResponseService->success([
            'uuid' => $uuid,
            'message' => 'Cart UUID generated successfully',
        ]);
    }
}
