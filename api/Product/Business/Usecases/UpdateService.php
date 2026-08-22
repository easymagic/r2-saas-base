<?php
namespace Product\Business\Usecases;

use Product\Business\Dtos\UpdateDto;
use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;

class UpdateService
{
    private ProductRepositoryInterface $productRepository;
    private ProductSupport $productSupport;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSupport $productSupport
    ) {
        $this->productRepository = $productRepository;
        $this->productSupport = $productSupport;
    }

    public function execute(UpdateDto $updateDto)
    {
        Contracts::requires($updateDto->id > 0, 'Product ID is required');

        $product = $this->productRepository->find($updateDto->id);
        Contracts::requireEntityFound($product, 'Product');

        $this->productSupport->assertProductPayload(
            $updateDto->name,
            $updateDto->description,
            $updateDto->price,
            $updateDto->old_price,
            $updateDto->stock_qty,
            $updateDto->category_id,
            $updateDto->user_id
        );

        Contracts::requiresInArray($updateDto->active, [0, 1], 'Active');

        $slug = $this->productSupport->normalizeSlug(
            $updateDto->slug !== '' ? $updateDto->slug : $updateDto->name
        );
        $this->productSupport->assertSlugAvailable($slug, $updateDto->id);
        $this->productSupport->assertCategoryExists($updateDto->category_id);

        $product->name = trim($updateDto->name);
        $product->description = trim($updateDto->description);
        $product->price = $updateDto->price;
        $product->old_price = $updateDto->old_price;
        $product->stock_qty = $updateDto->stock_qty;
        $product->slug = $slug;
        $product->user_id = $updateDto->user_id;
        $product->category_id = $updateDto->category_id;
        $product->active = $updateDto->active;

        $imageSlots = [
            'image_1' => $updateDto->image_1,
            'image_2' => $updateDto->image_2,
            'image_3' => $updateDto->image_3,
            'image_4' => $updateDto->image_4,
            'image_5' => $updateDto->image_5,
            'image_6' => $updateDto->image_6,
            'image_7' => $updateDto->image_7,
        ];

        foreach ($imageSlots as $field => $file) {
            $path = $this->productSupport->uploadImage($file, false, $field);
            if ($path !== '') {
                $product->$field = $path;
                if ($field === 'image_1') {
                    $product->image = $path;
                }
            }
        }

        return $this->productRepository->save($product);
    }
}
