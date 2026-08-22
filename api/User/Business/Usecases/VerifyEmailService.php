<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\VerifyEmailDto;
use User\Data\UserRepositoryInterface;

class VerifyEmailService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(VerifyEmailDto $verifyEmailDto)
    {
        $user = $this->userRepository->query([
            'email' => $verifyEmailDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires($user->otp == $verifyEmailDto->otp, 'OTP is incorrect');
        $user->status = 'active';
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
