<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use UserKyc\Data\UserKycRepositoryInterface;

class IsKycCompletedService
{
    private UserKycRepositoryInterface $userKycRepository;

    public function __construct(UserKycRepositoryInterface $userKycRepository)
    {
        $this->userKycRepository = $userKycRepository;
    }

    public function query(int $user_id)
    {
        Contracts::requires($user_id > 0, 'User ID is required');

        $query = $this->userKycRepository->query([
            'user_id' => $user_id,
            'approved' => 1,
        ]);

        return $query->count() > 0;
    }
}
