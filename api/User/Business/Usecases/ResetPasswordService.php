<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\ResetPasswordDto;
use User\Data\UserRepositoryInterface;

class ResetPasswordService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(ResetPasswordDto $resetPasswordDto)
    {
        $user = $this->userRepository->query([
            'email' => $resetPasswordDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires($user->otp == $resetPasswordDto->otp, 'OTP is incorrect');
        $user->password = password_hash($resetPasswordDto->password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
