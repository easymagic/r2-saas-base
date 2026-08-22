<?php
namespace Wallet\Business\Usecases;

use Wallet\Business\Dtos\LogDto;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletRepositoryInterface;

class LogService
{
    private WalletRepositoryInterface $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function execute(LogDto $logDto)
    {
        return $this->walletRepository->save(new WalletEntity([
            'user_id' => $logDto->user_id,
            'amount' => $logDto->amount,
            'reference' => $logDto->reference,
            'type' => $logDto->type,
            'description' => $logDto->description,
            'status' => $logDto->status,
        ]));
    }
}
