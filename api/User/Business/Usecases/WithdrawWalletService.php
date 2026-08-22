<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\WithdrawWalletDto;
use User\Business\Usecases\Mail\SendAccountWithdrawWalletToUserService;
use User\Data\UserRepositoryInterface;

class WithdrawWalletService
{
    private UserRepositoryInterface $userRepository;
    private SendAccountWithdrawWalletToUserService $sendAccountWithdrawWalletToUserService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SendAccountWithdrawWalletToUserService $sendAccountWithdrawWalletToUserService
    ) {
        $this->userRepository = $userRepository;
        $this->sendAccountWithdrawWalletToUserService = $sendAccountWithdrawWalletToUserService;
    }

    public function execute(WithdrawWalletDto $withdrawWalletDto)
    {
        $user = $this->userRepository->find($withdrawWalletDto->id);
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires(
            $user->wallet_balance >= $withdrawWalletDto->amount,
            'Balance is not enough'
        );
        $user->wallet_balance = $user->wallet_balance - $withdrawWalletDto->amount;
        $user->updated_at = date('Y-m-d H:i:s');
        $user = $this->userRepository->save($user);
        $this->sendAccountWithdrawWalletToUserService->execute(
            $user->id,
            $withdrawWalletDto->amount
        );
        return $user;
    }
}
