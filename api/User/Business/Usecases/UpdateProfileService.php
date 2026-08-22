<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\UpdateProfileDto;
use User\Data\UserRepositoryInterface;

class UpdateProfileService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(UpdateProfileDto $updateProfileDto)
    {
        $user = $this->userRepository->find($updateProfileDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->name = $updateProfileDto->name;
        $user->phone = $updateProfileDto->phone;
        $user->delivery_address = $updateProfileDto->delivery_address;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }
}
