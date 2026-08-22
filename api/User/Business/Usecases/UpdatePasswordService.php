<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\UpdatePasswordDto;
use User\Data\UserRepositoryInterface;

class UpdatePasswordService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(UpdatePasswordDto $updatePasswordDto)
    {
        $user = $this->userRepository->find($updatePasswordDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->password = password_hash($updatePasswordDto->password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
