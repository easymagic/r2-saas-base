<?php

namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Business\Dtos\CreateSchedulesDto;
use BnplPaymentSchedule\Business\Usecases\CreateSchedulesService;
use Cart\Business\Usecases\ClearCartService;
use Cart\Business\Usecases\GetCartTotalService;
use EcomOrder\Business\Dtos\CheckoutDto;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Shared\Contracts;
use User\Business\Dtos\WithdrawWalletDto;
use User\Business\Usecases\GetWalletBalanceService;
use User\Business\Usecases\WithdrawWalletService;

class CheckoutService
{

    private PaymentServiceInterface $paymentService;

    private GetCartTotalService $getCartTotalService;

    private EcomOrderSupport $ecomOrderSupport;

    private GetWalletBalanceService $getWalletBalanceService;

    private UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService;

    private WithdrawWalletService $withdrawWalletService;

    private CreateSchedulesService $createSchedulesService;

    private EcomOrderRepositoryInterface $ecomOrderRepository;

    private ClearCartService $clearCartService;

    public function __construct(
        PaymentServiceInterface $paymentService,
        GetCartTotalService $getCartTotalService,
        EcomOrderSupport $ecomOrderSupport,
        GetWalletBalanceService $getWalletBalanceService,
        UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService,
        WithdrawWalletService $withdrawWalletService,
        CreateSchedulesService $createSchedulesService,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        ClearCartService $clearCartService
    ) {
        $this->paymentService = $paymentService;
        $this->getCartTotalService = $getCartTotalService;
        $this->ecomOrderSupport = $ecomOrderSupport;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->updatePaymentStatusAsPaidService = $updatePaymentStatusAsPaidService;
        $this->withdrawWalletService = $withdrawWalletService;
        $this->createSchedulesService = $createSchedulesService;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->clearCartService = $clearCartService;
    }

    private function getCartTotal(string $uuid)
    {
        return round($this->getCartTotalService->query($uuid)
            + $this->ecomOrderSupport->getShippingFee()
            + $this->ecomOrderSupport->getServiceCharge(), 2);
    }


    public function execute(CheckoutDto $checkoutDto)
    {
        // Checkout implementation is currently pending (logic was commented out in the previous service).
        $type = $checkoutDto->type;
        Contracts::requires(in_array($type, ['bnpl', 'card', 'wallet']), 'Invalid checkout type');

        $this->handleBnplCheckout(($type === 'bnpl'), $checkoutDto);

        $this->handleCardCheckout(($type === 'card'), $checkoutDto);

        $this->handleWalletCheckout(($type === 'wallet'), $checkoutDto);

        $order = $this->createOrder($checkoutDto);

        $this->deductWalletBalance(($type === 'wallet'), $checkoutDto);

        $this->createPaymentSchedule(($type === 'bnpl'), $checkoutDto);

        $this->clearCartService->execute($checkoutDto->cart_uuid);

        return $order;
    }


    private function handleBnplCheckout(bool $condition, CheckoutDto $checkoutDto)
    {
        if ($condition) {
            Contracts::requires($checkoutDto->number_of_installment > 0, 'Number of installment is required');
            Contracts::requires($checkoutDto->number_of_installment > 1 && $checkoutDto->number_of_installment <= 12, 'Number of installment must be between 2 and 12');
        }
    }

    private function handleCardCheckout(bool $condition, CheckoutDto $checkoutDto)
    {
        if ($condition) {
            // die("Total: " . $this->getCartTotal($checkoutDto->cart_uuid));
            $this->paymentService->initiate(
                $checkoutDto->customer_email,
                $this->getCartTotal($checkoutDto->cart_uuid),
                $checkoutDto->reference
            );
            $payment_url = $this->paymentService->getAuthUrl();
            $checkoutDto->payment_url = $payment_url;
        }
    }

    private function handleWalletCheckout(bool $condition, CheckoutDto $checkoutDto)
    {
        if ($condition) {
            $wallet_balance = $this->getWalletBalanceService->query($checkoutDto->user_id);
            Contracts::requires($wallet_balance >= $this->getCartTotal($checkoutDto->cart_uuid), 'Wallet balance is not enough');
        }
    }

    private function deductWalletBalance(bool $condition, CheckoutDto $checkoutDto)
    {
        if ($condition) {
            $this->withdrawWalletService->execute(
                new WithdrawWalletDto(
                    $checkoutDto->user_id,
                    $this->getCartTotal($checkoutDto->cart_uuid)
                )
            );
            $this->updatePaymentStatusAsPaidService->execute($checkoutDto->order_id);
        }
    }

    private function createPaymentSchedule(bool $condition, CheckoutDto $checkoutDto)
    {
        if ($condition) {
            $this->createSchedulesService->execute(new CreateSchedulesDto(
                $checkoutDto->order_id,
                $checkoutDto->number_of_installment,
                round($this->getCartTotal($checkoutDto->cart_uuid) / $checkoutDto->number_of_installment, 2),
                $checkoutDto->reference,
                ""
            ));
        }
    }

    private function createOrder(CheckoutDto $checkoutDto) {
        $order = $this->ecomOrderRepository->save(new EcomOrderEntity([
            'user_id' => $checkoutDto->user_id,
            'type' => $checkoutDto->type,
            'number_of_installment' => $checkoutDto->number_of_installment,
            'customer_name' => $checkoutDto->customer_name,
            'customer_address' => $checkoutDto->customer_address,
            'customer_email' => $checkoutDto->customer_email,
            'reference' => $checkoutDto->reference,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
            'payment_url' => $checkoutDto->payment_url,
            'is_guest' => $checkoutDto->is_guest
        ]));
        $checkoutDto->order_id = $order->id; // update the order ID in the checkout DTO
        return $order;
    }
}
