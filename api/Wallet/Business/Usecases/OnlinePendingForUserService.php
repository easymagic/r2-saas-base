<?php
namespace Wallet\Business\Usecases;

use Wallet\Data\WalletRepositoryInterface;

class OnlinePendingForUserService
{
    private WalletRepositoryInterface $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function query(int $user_id)
    {
        return $this->walletRepository->query([
            'online' => true,
            'status' => 'pending',
            'user_id' => $user_id,
        ])->fetchAll();
    }
}
