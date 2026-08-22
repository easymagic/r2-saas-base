<?php
namespace UserKyc\Business\Usecases;

use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

/**
 * Shared helpers for UserKyc create/update use cases.
 */
class UserKycSupport
{
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function assertPayload(int $user_id, string $nin, string $store_name, string $description)
    {
        Contracts::requires($user_id > 0, 'User ID is required');
        Contracts::requiresNotNullOrEmpty(trim($nin), 'NIN');
        Contracts::requiresNotNullOrEmpty(trim($store_name), 'Store name');
        Contracts::requiresNotNullOrEmpty(trim($description), 'Description');
    }

    public function assertUserExists(int $user_id)
    {
        $user = $this->userRepository->find($user_id);
        Contracts::requireEntityFound($user, 'User');
    }

    /**
     * @param array $document
     * @param bool $required
     * @param string $label
     * @return string
     */
    public function uploadDocument(array $document, bool $required, string $label)
    {
        if (empty($document) || empty($document['tmp_name'])) {
            Contracts::requires(!$required, $label . ' is required');
            return '';
        }

        $path = '/uploads/user_kycs';
        $full_path = __DIR__ . '/../../../';

        $file_path = $this->fileUploadService->uploadFile($document, $path, $full_path);
        Contracts::requires((bool) $file_path, 'Failed to upload ' . $label);

        return (string) $file_path;
    }
}
