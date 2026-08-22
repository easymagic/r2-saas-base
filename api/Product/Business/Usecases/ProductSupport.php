<?php
namespace Product\Business\Usecases;

use Category\Data\CategoryRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\Contracts;
use Product\Data\ProductRepositoryInterface;

/**
 * Shared helpers for product create/update use cases.
 */
class ProductSupport
{
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function assertProductPayload(
        string $name,
        string $description,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id
    ) {
        Contracts::requiresNotNullOrEmpty(trim($name), 'Name');
        Contracts::requires(strlen($name) <= 100, 'Name should be less than 100 characters');
        Contracts::requiresNotNullOrEmpty(trim($description), 'Description');
        Contracts::requires(strlen($description) <= 5000, 'Description should be less than 5000 characters');
        Contracts::requires($price >= 0, 'Price cannot be negative');
        Contracts::requires($old_price >= 0, 'Old price cannot be negative');
        Contracts::requires($stock_qty >= 0, 'Stock quantity cannot be negative');
        Contracts::requires($category_id > 0, 'Category ID is required');
        Contracts::requires($user_id > 0, 'User ID is required');
        Contracts::requires(
            !($old_price > 0 && $old_price < $price),
            'Old price must be greater than price'
        );
    }

    /**
     * @param array $image
     * @param bool $required
     * @param string $label
     * @return string
     */
    public function uploadImage(array $image, bool $required, string $label = 'image')
    {
        if (empty($image) || empty($image['tmp_name'])) {
            Contracts::requires(!$required, $label . ' is required');
            return '';
        }

        $path = '/uploads/products';
        $full_path = __DIR__ . '/../../../';

        $image_path = $this->fileUploadService->uploadFile($image, $path, $full_path);
        Contracts::requires((bool) $image_path, 'Failed to upload ' . $label);

        return (string) $image_path;
    }

    public function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');
        Contracts::requires($slug !== '', 'Slug is required');
        return $slug;
    }

    public function assertSlugAvailable(string $slug, int $excludeId = 0)
    {
        $existing = $this->productRepository->query(['slug' => $slug])->fetchOne();
        Contracts::requires(
            $existing->isEmpty() || (int) $existing->id === $excludeId,
            'Slug is already in use'
        );
    }

    public function assertCategoryExists(int $category_id)
    {
        $category = $this->categoryRepository->find($category_id);
        Contracts::requireEntityFound($category, 'Category');
    }
}
