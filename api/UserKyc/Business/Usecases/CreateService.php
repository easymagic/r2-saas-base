<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use UserKyc\Business\Dtos\CreateDto;
use UserKyc\Data\UserKycEntity;
use UserKyc\Data\UserKycRepositoryInterface;

class CreateService
{
    private UserKycRepositoryInterface $userKycRepository;
    private UserKycSupport $userKycSupport;

    public function __construct(
        UserKycRepositoryInterface $userKycRepository,
        UserKycSupport $userKycSupport
    ) {
        $this->userKycRepository = $userKycRepository;
        $this->userKycSupport = $userKycSupport;
    }

    public function execute(CreateDto $createDto)
    {
        $user_id = $createDto->user_id;
        $nin = $createDto->nin;
        $store_name = $createDto->store_name;
        $description = $createDto->description;

        $this->userKycSupport->assertPayload($user_id, $nin, $store_name, $description);
        $this->userKycSupport->assertUserExists($user_id);

        $existing = $this->userKycRepository->query(['user_id' => $user_id])->fetchOne();
        Contracts::requires(
            $existing->isEmpty() || (int) $existing->approved !== -1,
            'You already have a KYC submission pending approval'
        );
        Contracts::requires(
            $existing->isEmpty() || (int) $existing->approved !== 1,
            'Your KYC is already approved'
        );
        Contracts::requires(
            $existing->isEmpty() || (int) $existing->approved !== 0,
            'Please update your existing rejected KYC submission'
        );

        $document1_path = $this->userKycSupport->uploadDocument($createDto->document1, false, 'document1');
        $document2_path = $this->userKycSupport->uploadDocument($createDto->document2, false, 'document2');
        $document3_path = $this->userKycSupport->uploadDocument($createDto->document3, false, 'document3');
        $document4_path = $this->userKycSupport->uploadDocument($createDto->document4, false, 'document4');
        $document5_path = $this->userKycSupport->uploadDocument($createDto->document5, false, 'document5');

        return $this->userKycRepository->save(new UserKycEntity([
            'user_id' => $user_id,
            'nin' => trim($nin),
            'store_name' => trim($store_name),
            'description' => trim($description),
            'document1' => $document1_path,
            'document2' => $document2_path,
            'document3' => $document3_path,
            'document4' => $document4_path,
            'document5' => $document5_path,
            'approved' => -1,
            'approved_by' => 0,
            'reject_reason' => '',
        ]));
    }
}
