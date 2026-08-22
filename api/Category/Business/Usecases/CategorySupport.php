<?php
namespace Category\Business\Usecases;

use Exception;
use Category\Data\CategoryRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

/**
 * Shared helpers for category create/update use cases.
 */
class CategorySupport
{
    private CategoryRepositoryInterface $categoryRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * @param array $image
     * @param bool $required
     * @return string
     */
    public function uploadImage(array $image, bool $required)
    {
        if (empty($image) || empty($image['tmp_name'])) {
            if ($required) {
                throw new Exception('Image is required');
            }
            return '';
        }

        $path = '/uploads/categories';
        $full_path = __DIR__ . '/../../../';

        $image_path = $this->fileUploadService->uploadFile($image, $path, $full_path);
        if (!$image_path) {
            throw new Exception('Failed to upload category image');
        }

        return (string) $image_path;
    }

    public function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');
        if ($slug === '') {
            throw new Exception('Slug is required');
        }
        return $slug;
    }

    public function assertSlugAvailable(string $slug, int $excludeId = 0)
    {
        $existing = $this->categoryRepository->query(['slug' => $slug])->fetchOne();
        if (!$existing->isEmpty() && (int) $existing->id !== $excludeId) {
            throw new Exception('Slug is already in use');
        }
    }

    public function assertParentExists(int $parent_id)
    {
        if ($parent_id <= 0) {
            return;
        }
        $parent = $this->categoryRepository->find($parent_id);
        if ($parent->isEmpty()) {
            throw new Exception('Parent category not found');
        }
    }
}
