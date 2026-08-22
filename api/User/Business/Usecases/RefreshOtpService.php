<?php
namespace User\Business\Usecases;

use User\Data\UserRepositoryInterface;

class RefreshOtpService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $user->otp = (string) rand(100000, 999999);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
