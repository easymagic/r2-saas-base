<?php
namespace SnappyOrder\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Business\Dtos\PayOrderFromWalletDto;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfOrderPaymentService;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Business\Dtos\WithdrawWalletDto;
use User\Business\Usecases\WithdrawWalletService;
use Wallet\Business\Dtos\LogDto as WalletLogDto;
use Wallet\Business\Usecases\LogService as WalletLogService;

class PayOrderFromWalletService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private WithdrawWalletService $withdrawWalletService;
    private WalletLogService $walletLogService;
    private NotifyCustomerOfOrderPaymentService $notifyCustomerOfOrderPaymentService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        WithdrawWalletService $withdrawWalletService,
        WalletLogService $walletLogService,
        NotifyCustomerOfOrderPaymentService $notifyCustomerOfOrderPaymentService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->withdrawWalletService = $withdrawWalletService;
        $this->walletLogService = $walletLogService;
        $this->notifyCustomerOfOrderPaymentService = $notifyCustomerOfOrderPaymentService;
    }

    public function execute(PayOrderFromWalletDto $payOrderFromWalletDto)
    {
        $order = $this->snappyOrderRepository->find($payOrderFromWalletDto->order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires((int) $order->price_adjustment_sent === 1, 'Price adjustment not sent');
        Contracts::requires(
            (int) $order->user_id === $payOrderFromWalletDto->user_id,
            'You are not authorized to pay for this order'
        );
        Contracts::requires($order->status === 'pending', 'Order can only be paid when status is pending');

        $amount = (float) $order->grand_total_naira;
        $this->withdrawWalletService->execute(new WithdrawWalletDto($payOrderFromWalletDto->user_id, $amount));
        $this->walletLogService->execute(new WalletLogDto(
            $payOrderFromWalletDto->user_id,
            $amount,
            uniqid('WALLET_WITHDRAWAL_'),
            'withdrawal',
            'Withdrawal from wallet for snappy order #' . $order->id,
            'approved'
        ));

        $order->status = 'paid';
        $order = $this->snappyOrderRepository->save($order);

        $this->notifyCustomerOfOrderPaymentService->execute($order->id);

        return $order;
    }
}
