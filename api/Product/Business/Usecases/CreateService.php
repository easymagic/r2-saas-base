<?php
namespace Product\Business\Usecases;

use Product\Business\Dtos\CreateDto;
use Product\Data\ProductEntity;
use Product\Data\ProductRepositoryInterface;

class CreateService
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

    public function execute(CreateDto $createDto)
    {
        $this->productSupport->assertProductPayload(
            $createDto->name,
            $createDto->description,
            $createDto->price,
            $createDto->old_price,
            $createDto->stock_qty,
            $createDto->category_id,
            $createDto->user_id
        );

        $slug = $this->productSupport->normalizeSlug(
            $createDto->slug !== '' ? $createDto->slug : $createDto->name
        );
        $this->productSupport->assertSlugAvailable($slug);
        $this->productSupport->assertCategoryExists($createDto->category_id);

        $image_1_path = $this->productSupport->uploadImage($createDto->image_1, true, 'image_1');
        $image_2_path = $this->productSupport->uploadImage($createDto->image_2, false, 'image_2');
        $image_3_path = $this->productSupport->uploadImage($createDto->image_3, false, 'image_3');
        $image_4_path = $this->productSupport->uploadImage($createDto->image_4, false, 'image_4');
        $image_5_path = $this->productSupport->uploadImage($createDto->image_5, false, 'image_5');
        $image_6_path = $this->productSupport->uploadImage($createDto->image_6, false, 'image_6');
        $image_7_path = $this->productSupport->uploadImage($createDto->image_7, false, 'image_7');

        return $this->productRepository->save(new ProductEntity([
            'name' => trim($createDto->name),
            'description' => trim($createDto->description),
            'image' => $image_1_path,
            'image_1' => $image_1_path,
            'image_2' => $image_2_path,
            'image_3' => $image_3_path,
            'image_4' => $image_4_path,
            'image_5' => $image_5_path,
            'image_6' => $image_6_path,
            'image_7' => $image_7_path,
            'price' => $createDto->price,
            'uuid' => uniqid('prd_', true),
            'old_price' => $createDto->old_price,
            'stock_qty' => $createDto->stock_qty,
            'active' => 0,
            'slug' => $slug,
            'user_id' => $createDto->user_id,
            'category_id' => $createDto->category_id,
        ]));
    }
}
