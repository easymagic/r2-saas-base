<?php
namespace User\Business\Usecases;

use User\Data\UserRepositoryInterface;

class FetchUsersAsAdminService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function query(array $filters = [])
    {
        return $this->userRepository->query($filters);
    }
}
