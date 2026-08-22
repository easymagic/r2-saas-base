<?php
namespace Wallet\Business\Usecases;

use Shared\Contracts;
use User\Data\UserRepositoryInterface;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

class TopUpOnlineService
{
    private WalletRepositoryInterface $walletRepository;
    private UserRepositoryInterface $userRepository;
    private PaymentServiceInterface $paymentService;

    public function __construct(
        WalletRepositoryInterface $walletRepository,
        UserRepositoryInterface $userRepository,
        PaymentServiceInterface $paymentService
    ) {
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
        $this->paymentService = $paymentService;
    }

    public function execute(TopUpOnlineDto $topUpOnlineDto)
    {
        $user = $this->userRepository->find($topUpOnlineDto->user_id);
        Contracts::requireEntityFound($user, 'User');

        $this->paymentService->initiate(
            $user->email,
            $topUpOnlineDto->amount,
            $topUpOnlineDto->reference
        );

        $payment_url = $this->paymentService->getAuthUrl();

        return $this->walletRepository->save(new WalletEntity([
            'user_id' => $topUpOnlineDto->user_id,
            'amount' => $topUpOnlineDto->amount,
            'reference' => $topUpOnlineDto->reference,
            'type' => 'online',
            'description' => $topUpOnlineDto->description,
            'status' => 'pending',
            'payment_url' => $payment_url,
        ]));
    }
}
