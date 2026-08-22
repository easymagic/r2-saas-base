<?php
namespace UserKyc\Business\Usecases;

use UserKyc\Data\UserKycRepositoryInterface;

class FetchRejectedService
{
    private UserKycRepositoryInterface $userKycRepository;

    public function __construct(UserKycRepositoryInterface $userKycRepository)
    {
        $this->userKycRepository = $userKycRepository;
    }

    public function query()
    {
        return $this->userKycRepository->query(['approved' => 0]);
    }
}
