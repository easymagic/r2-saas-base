<?php

namespace Product\Business;

use Exception;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use Product\Data\ProductRepositoryInterface;
use Product\Data\ProductEntity;
use Product\Data\ProductMigrationRepositoryInterface;
use Category\Data\CategoryRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

/**
 * @extends AbstractBaseService<ProductEntity, ProductRepositoryInterface>
 */
class ProductService extends AbstractBaseService implements ProductServiceInterface
{
    private ProductMigrationRepositoryInterface $productMigrationRepositoryInterface;
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        ProductMigrationRepositoryInterface $productMigrationRepositoryInterface,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        parent::__construct($productRepository);
        $this->productMigrationRepositoryInterface = $productMigrationRepositoryInterface;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function migrate()
    {
        return $this->productMigrationRepositoryInterface->migrate();
    }

    /**
     * @param string $name
     * @param string $description
     * @param array $image
     * @param float $price
     * @param float $old_price
     * @param int $stock_qty
     * @param int $category_id
     * @param int $user_id
     * @param string $slug
     * @return ProductEntity
     */
    public function create(
        string $name,
        string $description,
        array $image,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug
    ) {
        $this->assertProductPayload($name, $description, $price, $old_price, $stock_qty, $category_id, $user_id);

        $slug = $this->normalizeSlug($slug !== '' ? $slug : $name);
        $this->assertSlugAvailable($slug);
        $this->assertCategoryExists($category_id);

        $image_path = $this->uploadImage($image, true);

        return $this->productRepository->save(0, [
            'name' => trim($name),
            'description' => trim($description),
            'image' => $image_path,
            'price' => $price,
            'uuid' => uniqid('prd_', true),
            'old_price' => $old_price,
            'stock_qty' => $stock_qty,
            'active' => 1,
            'slug' => $slug,
            'user_id' => $user_id,
            'category_id' => $category_id,
        ]);
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param array $image
     * @param float $price
     * @param float $old_price
     * @param int $stock_qty
     * @param int $category_id
     * @param int $user_id
     * @param string $slug
     * @return ProductEntity
     */
    public function update(
        int $id,
        string $name,
        string $description,
        array $image,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug
    ) {
        if (empty($id)) {
            throw new Exception('Product ID is required');
        }

        $product = $this->productRepository->find($id);
        if ($product->isEmpty()) {
            throw new Exception('Product not found');
        }

        $this->assertProductPayload($name, $description, $price, $old_price, $stock_qty, $category_id, $user_id);

        $slug = $this->normalizeSlug($slug !== '' ? $slug : $name);
        $this->assertSlugAvailable($slug, $id);
        $this->assertCategoryExists($category_id);

        $payload = [
            'name' => trim($name),
            'description' => trim($description),
            'price' => $price,
            'old_price' => $old_price,
            'stock_qty' => $stock_qty,
            'slug' => $slug,
            'user_id' => $user_id,
            'category_id' => $category_id,
        ];

        $image_path = $this->uploadImage($image, false);
        if ($image_path !== '') {
            $payload['image'] = $image_path;
        }

        return $this->productRepository->save($product->id, $payload);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id)
    {
        if (empty($id)) {
            throw new Exception('Product ID is required');
        }

        $product = $this->productRepository->find($id);
        if ($product->isEmpty()) {
            throw new Exception('Product not found');
        }

        $this->productRepository->delete($id);
        return true;
    }

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForAdmin(array $filters = [])
    {
        return $this->productRepository->query($filters);
    }

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForFrontend(array $filters = [])
    {
        $filters['active'] = 1;
        return $this->productRepository->query($filters);
    }

    /**
     * @param int $merchant_id
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForMerchant(int $merchant_id, array $filters = [])
    {
        if (empty($merchant_id)) {
            throw new Exception('Merchant ID is required');
        }

        $filters['user_id'] = $merchant_id;
        return $this->productRepository->query($filters);
    }

    /**
     * @param int $id
     * @return ProductEntity
     */
    public function findById(int $id)
    {
        if (empty($id)) {
            throw new Exception('Product ID is required');
        }

        $product = $this->productRepository->find($id);
        if ($product->isEmpty()) {
            throw new Exception('Product not found');
        }

        return $product;
    }

    /**
     * @param string $slug
     * @return ProductEntity
     */
    public function findBySlug(string $slug)
    {
        $slug = trim($slug);
        if ($slug === '') {
            throw new Exception('Slug is required');
        }

        $product = $this->productRepository->findBy('slug', $slug);
        if ($product->isEmpty()) {
            throw new Exception('Product not found');
        }

        return $product;
    }

    /**
     * @param string $uuid
     * @return ProductEntity
     */
    public function findByUuid(string $uuid)
    {
        $uuid = trim($uuid);
        if ($uuid === '') {
            throw new Exception('UUID is required');
        }

        $product = $this->productRepository->findBy('uuid', $uuid);
        if ($product->isEmpty()) {
            throw new Exception('Product not found');
        }

        return $product;
    }

    private function assertProductPayload(
        string $name,
        string $description,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id
    ) {
        if (trim($name) === '') {
            throw new Exception('Name is required');
        }
        if (strlen($name) > 100) {
            throw new Exception('Name should be less than 100 characters');
        }
        if (trim($description) === '') {
            throw new Exception('Description is required');
        }
        if (strlen($description) > 5000) {
            throw new Exception('Description should be less than 5000 characters');
        }
        if ($price < 0) {
            throw new Exception('Price cannot be negative');
        }
        if ($old_price < 0) {
            throw new Exception('Old price cannot be negative');
        }
        if ($stock_qty < 0) {
            throw new Exception('Stock quantity cannot be negative');
        }
        if ($category_id <= 0) {
            throw new Exception('Category ID is required');
        }
        if ($user_id <= 0) {
            throw new Exception('User ID is required');
        }
        // old price must be greater than price
        if ($old_price < $price) {
            throw new Exception('Old price must be greater than price');
        }
    }

    /**
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

        $path = '/uploads/products';
        $full_path = __DIR__ . '/../../';

        $image_path = $this->fileUploadService->uploadFile($image, $path, $full_path);
        if (!$image_path) {
            throw new Exception('Failed to upload product image');
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
        $existing = $this->productRepository->findBy('slug', $slug);
        if (!$existing->isEmpty() && (int) $existing->id !== $excludeId) {
            throw new Exception('Slug is already in use');
        }
    }

    private function assertCategoryExists(int $category_id)
    {
        $category = $this->categoryRepository->find($category_id);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }
    }
}
