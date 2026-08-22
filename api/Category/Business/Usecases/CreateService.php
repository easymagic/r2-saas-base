<?php
namespace Category\Business\Usecases;

use Category\Business\Dtos\CreateDto;
use Category\Data\CategoryEntity;
use Category\Data\CategoryRepositoryInterface;

class CreateService
{
    private CategoryRepositoryInterface $categoryRepository;
    private CategorySupport $categorySupport;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategorySupport $categorySupport
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categorySupport = $categorySupport;
    }

    public function execute(CreateDto $createDto)
    {
        $slug = $this->categorySupport->normalizeSlug(
            $createDto->slug !== '' ? $createDto->slug : $createDto->name
        );
        $this->categorySupport->assertSlugAvailable($slug);
        $this->categorySupport->assertParentExists($createDto->parent_id);

        $image_path = $this->categorySupport->uploadImage($createDto->image, true);

        return $this->categoryRepository->save(new CategoryEntity([
            'name' => $createDto->name,
            'parent_id' => $createDto->parent_id > 0 ? $createDto->parent_id : 0,
            'description' => $createDto->description,
            'image' => $image_path,
            'slug' => $slug,
            'active' => 1,
        ]));
    }
}
