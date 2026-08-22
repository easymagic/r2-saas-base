<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\ChangePasswordDto;
use User\Data\UserRepositoryInterface;

class ChangePasswordService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(ChangePasswordDto $changePasswordDto)
    {
        $user = $this->userRepository->find($changePasswordDto->id);
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires(
            password_verify($changePasswordDto->old_password, $user->password),
            'Old password is incorrect'
        );
        $user->password = password_hash($changePasswordDto->new_password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
