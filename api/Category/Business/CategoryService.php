<?php

namespace Category\Business;

use Exception;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use Category\Data\CategoryRepositoryInterface;
use Category\Data\CategoryEntity;
use Category\Data\CategoryMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

/**
 * @extends AbstractBaseService<CategoryEntity, CategoryRepositoryInterface>
 */
class CategoryService extends AbstractBaseService implements CategoryServiceInterface
{
    private CategoryMigrationRepositoryInterface $categoryMigrationRepositoryInterface;
    private CategoryRepositoryInterface $categoryRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        CategoryMigrationRepositoryInterface $categoryMigrationRepositoryInterface,
        CategoryRepositoryInterface $categoryRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        parent::__construct($categoryRepository);
        $this->categoryMigrationRepositoryInterface = $categoryMigrationRepositoryInterface;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function migrate()
    {
        return $this->categoryMigrationRepositoryInterface->migrate();
    }

    /**
     * @param string $name
     * @param int $parent_id
     * @param string $description
     * @param array $image
     * @param string $slug
     * @return CategoryEntity
     */
    public function create(
        string $name,
        int $parent_id,
        string $description,
        array $image,
        string $slug
    ) {
        if (trim($name) === '') {
            throw new Exception('Name is required');
        }

        // name should be less than 100 characters
        if (strlen($name) > 100) {
            throw new Exception('Name should be less than 100 characters');
        }

        // description should not be empty
        if (trim($description) === '') {
            throw new Exception('Description is required');
        }

        // description should be less than 1000 characters
        if (strlen($description) > 1000) {
            throw new Exception('Description should be less than 1000 characters');
        }

        $slug = $this->normalizeSlug($slug !== '' ? $slug : $name);
        $this->assertSlugAvailable($slug);
        $this->assertParentExists($parent_id);

        $image_path = $this->uploadImage($image, true);

        return $this->categoryRepository->save(0, [
            'name' => trim($name),
            'parent_id' => $parent_id > 0 ? $parent_id : null,
            'description' => $description,
            'image' => $image_path,
            'slug' => $slug,
            'active' => 1,
        ]);
    }

    /**
     * @param int $id
     * @param string $name
     * @param int $parent_id
     * @param string $description
     * @param array $image
     * @param string $slug
     * @return CategoryEntity
     */
    public function update(
        int $id,
        string $name,
        int $parent_id,
        string $description,
        array $image,
        string $slug,
        int $active
    ) {
        if (empty($id)) {
            throw new Exception('Category ID is required');
        }
        if (trim($name) === '') {
            throw new Exception('Name is required');
        }

        $category = $this->categoryRepository->find($id);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }

        if ($parent_id > 0 && $parent_id === $id) {
            throw new Exception('Category cannot be its own parent');
        }

        if (empty($active)) {
            $active = 0;
        }

        $slug = $this->normalizeSlug($slug !== '' ? $slug : $name);
        $this->assertSlugAvailable($slug, $id);
        $this->assertParentExists($parent_id);

        $payload = [
            'name' => trim($name),
            'parent_id' => $parent_id > 0 ? $parent_id : null,
            'description' => $description,
            'slug' => $slug,
            'active' => $active,
        ];

        $image_path = $this->uploadImage($image, false);
        if ($image_path !== '') {
            $payload['image'] = $image_path;
        }

        return $this->categoryRepository->save($category->id, $payload);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id)
    {
        if (empty($id)) {
            throw new Exception('Category ID is required');
        }

        $category = $this->categoryRepository->find($id);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }

        $children = $this->categoryRepository->query(['parent_id' => $id]);
        if ($children->count() > 0) {
            throw new Exception('Cannot delete a category that has child categories');
        }

        $this->categoryRepository->delete($id);
        return true;
    }

    /**
     * @param array $filters
     * @return QueryObject<CategoryEntity>
     */
    public function fetchForAdmin(array $filters = [])
    {
        return $this->categoryRepository->query($filters);
    }

    /**
     * @param array $filters
     * @return QueryObject<CategoryEntity>
     */
    public function fetchForFrontend(array $filters = [])
    {
        $filters['active'] = 1;
        return $this->categoryRepository->query($filters);
    }

    /**
     * @param int $id
     * @return CategoryEntity
     */
    public function findById(int $id)
    {
        if (empty($id)) {
            throw new Exception('Category ID is required');
        }

        $category = $this->categoryRepository->find($id);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }

        return $category;
    }

    /**
     * @param string $slug
     * @return CategoryEntity
     */
    public function findBySlug(string $slug)
    {
        $slug = trim($slug);
        if ($slug === '') {
            throw new Exception('Slug is required');
        }

        $category = $this->categoryRepository->findBy('slug', $slug);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }

        return $category;
    }

    /**
     * Upload category image using FileUploadServiceInterface (SnappyOrder pattern).
     * @param array $image
     * @param bool $required
     * @return string
     */
    private function uploadImage(array $image, bool $required)
    {
        if (empty($image) || empty($image['tmp_name'])) {
            if ($required) {
                throw new Exception('Image is required');
            }
            return '';
        }

        $path = '/uploads/categories';
        $full_path = __DIR__ . '/../../';

        $image_path = $this->fileUploadService->uploadFile($image, $path, $full_path);
        if (!$image_path) {
            throw new Exception('Failed to upload category image');
        }

        return (string) $image_path;
    }

    private function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');
        if ($slug === '') {
            throw new Exception('Slug is required');
        }
        return $slug;
    }

    private function assertSlugAvailable(string $slug, int $excludeId = 0)
    {
        $existing = $this->categoryRepository->findBy('slug', $slug);
        if (!$existing->isEmpty() && (int) $existing->id !== $excludeId) {
            throw new Exception('Slug is already in use');
        }
    }

    private function assertParentExists(int $parent_id)
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
