<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use UserKyc\Data\UserKycRepositoryInterface;

class FetchForUserService
{
    private UserKycRepositoryInterface $userKycRepository;

    public function __construct(UserKycRepositoryInterface $userKycRepository)
    {
        $this->userKycRepository = $userKycRepository;
    }

    public function query(int $user_id)
    {
        Contracts::requires($user_id > 0, 'User ID is required');

        return $this->userKycRepository->query(['user_id' => $user_id]);
    }
}
