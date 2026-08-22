<?php
namespace User\Business\Usecases;

use User\Data\UserRepositoryInterface;

class GetWalletBalanceService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function query(int $id)
    {
        $user = $this->userRepository->find($id);
        return $user->wallet_balance;
    }
}
