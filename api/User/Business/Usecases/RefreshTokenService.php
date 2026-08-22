<?php
namespace User\Business\Usecases;

use User\Data\UserRepositoryInterface;

class RefreshTokenService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $token = bin2hex(random_bytes(32));
        $user->token = $userId . "_" . $token;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
