<?php

namespace UserKyc\Business;

use Exception;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use User\Data\UserRepositoryInterface;
use UserKyc\Data\UserKycEntity;
use UserKyc\Data\UserKycMigrationRepositoryInterface;
use UserKyc\Data\UserKycRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

/**
 * @extends AbstractBaseService<UserKycEntity, UserKycRepositoryInterface>
 */
class UserKycService extends AbstractBaseService implements UserKycServiceInterface
{
    private UserKycMigrationRepositoryInterface $userKycMigrationRepositoryInterface;
    private UserKycRepositoryInterface $userKycRepository;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;
    private UserKycNotificationServiceInterface $userKycNotificationService;

    public function __construct(
        UserKycMigrationRepositoryInterface $userKycMigrationRepositoryInterface,
        UserKycRepositoryInterface $userKycRepository,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService,
        UserKycNotificationServiceInterface $userKycNotificationService
    ) {
        parent::__construct($userKycRepository);
        $this->userKycMigrationRepositoryInterface = $userKycMigrationRepositoryInterface;
        $this->userKycRepository = $userKycRepository;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->userKycNotificationService = $userKycNotificationService;
    }

    public function migrate()
    {
        return $this->userKycMigrationRepositoryInterface->migrate();
    }

    public function create(
        int $user_id,
        string $nin,
        string $store_name,
        string $description,
        array $document1,
        array $document2,
        array $document3,
        array $document4,
        array $document5
    ) {
        $this->assertPayload($user_id, $nin, $store_name, $description);
        $this->assertUserExists($user_id);

        $existing = $this->userKycRepository->findBy('user_id', (string) $user_id);
        if (!$existing->isEmpty() && (int) $existing->approved === -1) {
            throw new Exception('You already have a KYC submission pending approval');
        }
        if (!$existing->isEmpty() && (int) $existing->approved === 1) {
            throw new Exception('Your KYC is already approved');
        }
        if (!$existing->isEmpty() && (int) $existing->approved === 0) {
            throw new Exception('Please update your existing rejected KYC submission');
        }

        $document1_path = $this->uploadDocument($document1, false, 'document1');
        $document2_path = $this->uploadDocument($document2, false, 'document2');
        $document3_path = $this->uploadDocument($document3, false, 'document3');
        $document4_path = $this->uploadDocument($document4, false, 'document4');
        $document5_path = $this->uploadDocument($document5, false, 'document5');

        return $this->userKycRepository->save(0, [
            'user_id' => $user_id,
            'nin' => trim($nin),
            'store_name' => trim($store_name),
            'description' => trim($description),
            'document1' => $document1_path !== '' ? $document1_path : null,
            'document2' => $document2_path !== '' ? $document2_path : null,
            'document3' => $document3_path !== '' ? $document3_path : null,
            'document4' => $document4_path !== '' ? $document4_path : null,
            'document5' => $document5_path !== '' ? $document5_path : null,
            'approved' => -1,
            'approved_by' => null,
            'reject_reason' => null,
        ]);
    }

    public function update(
        int $id,
        int $user_id,
        string $nin,
        string $store_name,
        string $description,
        array $document1,
        array $document2,
        array $document3,
        array $document4,
        array $document5
    ) {
        if (empty($id)) {
            throw new Exception('KYC ID is required');
        }

        $kyc = $this->userKycRepository->find($id);
        if ($kyc->isEmpty()) {
            throw new Exception('KYC record not found');
        }

        if ((int) $kyc->user_id !== $user_id) {
            throw new Exception('You are not authorized to update this KYC');
        }

        if ((int) $kyc->approved === 1) {
            throw new Exception('Approved KYC cannot be updated');
        }

        $this->assertPayload($user_id, $nin, $store_name, $description);

        $payload = [
            'nin' => trim($nin),
            'store_name' => trim($store_name),
            'description' => trim($description),
            'approved' => -1,
            'approved_by' => null,
            'reject_reason' => null,
        ];

        $documents = [
            'document1' => $document1,
            'document2' => $document2,
            'document3' => $document3,
            'document4' => $document4,
            'document5' => $document5,
        ];

        foreach ($documents as $field => $file) {
            $path = $this->uploadDocument($file, false, $field);
            if ($path !== '') {
                $payload[$field] = $path;
            }
        }

        return $this->userKycRepository->save($kyc->id, $payload);
    }

    public function approve(int $id, int $approved_by)
    {
        if (empty($id)) {
            throw new Exception('KYC ID is required');
        }
        if (empty($approved_by)) {
            throw new Exception('Approver ID is required');
        }

        $kyc = $this->userKycRepository->find($id);
        if ($kyc->isEmpty()) {
            throw new Exception('KYC record not found');
        }

        if ((int) $kyc->approved === 1) {
            throw new Exception('KYC is already approved');
        }

        $admin = $this->userRepository->find($approved_by);
        if ($admin->isEmpty()) {
            throw new Exception('Approver not found');
        }

        $kyc = $this->userKycRepository->save($kyc->id, [
            'approved' => 1,
            'approved_by' => $approved_by,
            'reject_reason' => null,
        ]);

        $this->userKycNotificationService->sendApproveNotification((int) $kyc->id);

        return $kyc;
    }

    public function reject(int $id, int $rejected_by, string $reject_reason)
    {
        if (empty($id)) {
            throw new Exception('KYC ID is required');
        }
        if (empty($rejected_by)) {
            throw new Exception('Rejector ID is required');
        }
        if (trim($reject_reason) === '') {
            throw new Exception('Reject reason is required');
        }

        $kyc = $this->userKycRepository->find($id);
        if ($kyc->isEmpty()) {
            throw new Exception('KYC record not found');
        }

        if ((int) $kyc->approved === 1) {
            throw new Exception('Approved KYC cannot be rejected');
        }

        $admin = $this->userRepository->find($rejected_by);
        if ($admin->isEmpty()) {
            throw new Exception('Rejector not found');
        }

        $kyc = $this->userKycRepository->save($kyc->id, [
            'approved' => 0,
            'approved_by' => $rejected_by,
            'reject_reason' => trim($reject_reason),
        ]);

        $this->userKycNotificationService->sendRejectNotification((int) $kyc->id);

        return $kyc;
    }

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForApproval()
    {
        return $this->userKycRepository->query(['approved' => -1]);
    }

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchApproved()
    {
        return $this->userKycRepository->query(['approved' => 1]);
    }

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchRejected()
    {
        return $this->userKycRepository->query(['approved' => 0]);
    }

    /**
     * @param int $user_id
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForUser(int $user_id)
    {
        if (empty($user_id)) {
            throw new Exception('User ID is required');
        }

        return $this->userKycRepository->query(['user_id' => $user_id]);
    }

    private function assertPayload(int $user_id, string $nin, string $store_name, string $description)
    {
        if ($user_id <= 0) {
            throw new Exception('User ID is required');
        }
        if (trim($nin) === '') {
            throw new Exception('NIN is required');
        }
        if (trim($store_name) === '') {
            throw new Exception('Store name is required');
        }
        if (trim($description) === '') {
            throw new Exception('Description is required');
        }
    }

    private function assertUserExists(int $user_id)
    {
        $user = $this->userRepository->find($user_id);
        if ($user->isEmpty()) {
            throw new Exception('User not found');
        }
    }

    /**
     * @param array $document
     * @param bool $required
     * @param string $label
     * @return string
     */
    private function uploadDocument(array $document, bool $required, string $label)
    {
        if (empty($document) || empty($document['tmp_name'])) {
            if ($required) {
                throw new Exception($label . ' is required');
            }
            return '';
        }

        $path = '/uploads/user_kycs';
        $full_path = __DIR__ . '/../../';

        $file_path = $this->fileUploadService->uploadFile($document, $path, $full_path);
        if (!$file_path) {
            throw new Exception('Failed to upload ' . $label);
        }

        return (string) $file_path;
    }
}
