<?php

namespace Presentation\Http\Controllers\Web;

use Cart\Business\Dtos\AddToCartDto;
use Cart\Business\Dtos\RemoveFromCartDto;
use Cart\Business\Usecases\AddToCartService;
use Cart\Business\Usecases\ClearCartService;
use Cart\Business\Usecases\GenerateCartUuidService;
use Cart\Business\Usecases\GetCartService;
use Cart\Business\Usecases\GetCartTotalService;
use Cart\Business\Usecases\RemoveFromCartService;
use EcomOrder\Business\Dtos\CheckoutDto;
use EcomOrder\Business\Usecases\CheckoutService;
use EcomOrder\Business\Usecases\EcomOrderSupport;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use Product\Business\Usecases\FindByIdService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class CartWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private GenerateCartUuidService $generateCartUuidService;
    private GetCartService $getCartService;
    private GetCartTotalService $getCartTotalService;
    private AddToCartService $addToCartService;
    private RemoveFromCartService $removeFromCartService;
    private ClearCartService $clearCartService;
    private CheckoutService $checkoutService;
    private FindByIdService $findProductByIdService;
    private EcomOrderSupport $ecomOrderSupport;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        GenerateCartUuidService $generateCartUuidService,
        GetCartService $getCartService,
        GetCartTotalService $getCartTotalService,
        AddToCartService $addToCartService,
        RemoveFromCartService $removeFromCartService,
        ClearCartService $clearCartService,
        CheckoutService $checkoutService,
        FindByIdService $findProductByIdService,
        EcomOrderSupport $ecomOrderSupport,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->generateCartUuidService = $generateCartUuidService;
        $this->getCartService = $getCartService;
        $this->getCartTotalService = $getCartTotalService;
        $this->addToCartService = $addToCartService;
        $this->removeFromCartService = $removeFromCartService;
        $this->clearCartService = $clearCartService;
        $this->checkoutService = $checkoutService;
        $this->findProductByIdService = $findProductByIdService;
        $this->ecomOrderSupport = $ecomOrderSupport;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    private function cartUuid()
    {
        $uuid = WebSession::cartUuid();
        if ($uuid === '') {
            $uuid = $this->generateCartUuidService->execute();
            WebSession::setCartUuid($uuid);
        }
        return $uuid;
    }

    private function userLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function cartLinesWithProducts($uuid)
    {
        $lines = $this->getCartService->query($uuid);
        if (!is_array($lines)) {
            return [];
        }
        $enriched = [];
        foreach ($lines as $line) {
            try {
                $product = $this->findProductByIdService->query((int) $line->product_id);
                $enriched[] = ['line' => $line, 'product' => $product];
            } catch (\Exception $e) {
                $enriched[] = ['line' => $line, 'product' => null];
            }
        }
        return $enriched;
    }

    private function cartTotals($uuid)
    {
        $subtotal = (float) $this->getCartTotalService->query($uuid);
        $shipping = (float) $this->ecomOrderSupport->getShippingFee();
        $service = (float) $this->ecomOrderSupport->getServiceCharge();
        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'service' => $service,
            'total' => round($subtotal + $shipping + $service, 2),
        ];
    }

    public function index()
    {
        $uuid = $this->cartUuid();
        $this->userLayout('cart/index', [
            'title' => 'Cart',
            'subtitle' => 'Review items before checkout',
            'nav' => 'cart',
            'lines' => $this->cartLinesWithProducts($uuid),
            'totals' => $this->cartTotals($uuid),
        ]);
    }

    public function add()
    {
        try {
            $this->addToCartService->execute(new AddToCartDto(
                $this->cartUuid(),
                (int) $this->request->get('product_id'),
                max(1, (int) $this->request->get('qty', 1))
            ));
            WebSession::flash('success', 'Added to cart.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        $redirect = trim((string) $this->request->get('redirect', '/cart'));
        if ($redirect === '' || $redirect[0] !== '/' || strpos($redirect, '//') !== false) {
            $redirect = '/cart';
        }
        WebSession::redirect($redirect);
    }

    public function remove()
    {
        try {
            $this->removeFromCartService->execute(new RemoveFromCartDto(
                $this->cartUuid(),
                (int) $this->request->get('product_id')
            ));
            WebSession::flash('success', 'Item removed.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/cart');
    }

    public function clear()
    {
        try {
            $this->clearCartService->execute($this->cartUuid());
            WebSession::flash('success', 'Cart cleared.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/cart');
    }

    public function checkoutForm()
    {
        $uuid = $this->cartUuid();
        $user = $this->apiCredentialService->getAuthUser();
        $lines = $this->cartLinesWithProducts($uuid);
        if (empty($lines)) {
            WebSession::flash('error', 'Your cart is empty.');
            WebSession::redirect('/shop');
        }
        $this->userLayout('cart/checkout', [
            'title' => 'Checkout',
            'subtitle' => 'Complete your purchase',
            'nav' => 'cart',
            'lines' => $lines,
            'totals' => $this->cartTotals($uuid),
            'old' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_address' => isset($user->delivery_address) ? $user->delivery_address : '',
                'type' => 'wallet',
                'number_of_installment' => 3,
            ],
        ]);
    }

    public function checkout()
    {
        $uuid = $this->cartUuid();
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $order = $this->checkoutService->execute(new CheckoutDto(
                (int) $user->id,
                (string) $this->request->get('type', 'wallet'),
                (int) $this->request->get('number_of_installment', 0),
                (string) $this->request->get('customer_name'),
                (string) $this->request->get('customer_address'),
                (string) $this->request->get('customer_email'),
                $uuid
            ));
            WebSession::setCartUuid($this->generateCartUuidService->execute());
            if (!empty($order->payment_url)) {
                header('Location: ' . $order->payment_url);
                exit;
            }
            WebSession::flash('success', 'Order #' . $order->id . ' placed.');
            WebSession::redirect('/ecom-orders/' . $order->id);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/cart/checkout');
        }
    }

}
