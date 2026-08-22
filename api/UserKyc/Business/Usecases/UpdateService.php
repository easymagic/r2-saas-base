<?php
namespace UserKyc\Business\Usecases;

use Shared\Contracts;
use UserKyc\Business\Dtos\UpdateDto;
use UserKyc\Data\UserKycRepositoryInterface;

class UpdateService
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

    public function execute(UpdateDto $updateDto)
    {
        $id = $updateDto->id;
        $user_id = $updateDto->user_id;
        $nin = $updateDto->nin;
        $store_name = $updateDto->store_name;
        $description = $updateDto->description;

        Contracts::requires($id > 0, 'KYC ID is required');

        $kyc = $this->userKycRepository->find($id);
        Contracts::requireEntityFound($kyc, 'KYC record');
        Contracts::requires((int) $kyc->user_id === $user_id, 'You are not authorized to update this KYC');
        Contracts::requires((int) $kyc->approved !== 1, 'Approved KYC cannot be updated');

        $this->userKycSupport->assertPayload($user_id, $nin, $store_name, $description);

        $kyc->nin = trim($nin);
        $kyc->store_name = trim($store_name);
        $kyc->description = trim($description);
        $kyc->approved = -1;
        $kyc->approved_by = 0;
        $kyc->reject_reason = '';

        $documents = [
            'document1' => $updateDto->document1,
            'document2' => $updateDto->document2,
            'document3' => $updateDto->document3,
            'document4' => $updateDto->document4,
            'document5' => $updateDto->document5,
        ];

        foreach ($documents as $field => $file) {
            $path = $this->userKycSupport->uploadDocument($file, false, $field);
            if ($path !== '') {
                $kyc->$field = $path;
            }
        }

        return $this->userKycRepository->save($kyc);
    }
}
