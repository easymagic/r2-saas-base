<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\TopUpWalletDto;
use User\Business\Usecases\Mail\SendAccountTopUpWalletToUserService;
use User\Data\UserRepositoryInterface;

class TopUpWalletService
{
    private UserRepositoryInterface $userRepository;
    private SendAccountTopUpWalletToUserService $sendAccountTopUpWalletToUserService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SendAccountTopUpWalletToUserService $sendAccountTopUpWalletToUserService
    ) {
        $this->userRepository = $userRepository;
        $this->sendAccountTopUpWalletToUserService = $sendAccountTopUpWalletToUserService;
    }

    public function execute(TopUpWalletDto $topUpWalletDto)
    {
        $user = $this->userRepository->find($topUpWalletDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->wallet_balance = $user->wallet_balance + $topUpWalletDto->amount;
        $user->updated_at = date('Y-m-d H:i:s');
        $user = $this->userRepository->save($user);
        $this->sendAccountTopUpWalletToUserService->execute(
            $user->id,
            $topUpWalletDto->amount
        );
        return $user;
    }
}
