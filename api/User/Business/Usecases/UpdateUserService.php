<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\UpdateUserDto;
use User\Data\UserRepositoryInterface;

class UpdateUserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(UpdateUserDto $updateUserDto)
    {
        $user = $this->userRepository->find($updateUserDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->name = $updateUserDto->name;
        $user->phone = $updateUserDto->phone;
        $user->delivery_address = $updateUserDto->delivery_address;
        $user->social_security_number = $updateUserDto->social_security_number;
        $user->role = $updateUserDto->role;
        $user->status = $updateUserDto->status;
        $user->country_code = $updateUserDto->country_code;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
