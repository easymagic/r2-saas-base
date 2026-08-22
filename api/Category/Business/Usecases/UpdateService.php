<?php
namespace Category\Business\Usecases;

use Exception;
use Category\Business\Dtos\UpdateDto;
use Category\Data\CategoryRepositoryInterface;
use Shared\Contracts;

class UpdateService
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

    public function execute(UpdateDto $updateDto)
    {
        $category = $this->categoryRepository->find($updateDto->id);
        Contracts::requireEntityFound($category, 'Category');

        if ($updateDto->parent_id > 0 && $updateDto->parent_id === $updateDto->id) {
            throw new Exception('Category cannot be its own parent');
        }

        $active = empty($updateDto->active) ? 0 : $updateDto->active;

        $slug = $this->categorySupport->normalizeSlug(
            $updateDto->slug !== '' ? $updateDto->slug : $updateDto->name
        );
        $this->categorySupport->assertSlugAvailable($slug, $updateDto->id);
        $this->categorySupport->assertParentExists($updateDto->parent_id);

        $category->name = $updateDto->name;
        $category->parent_id = $updateDto->parent_id > 0 ? $updateDto->parent_id : 0;
        $category->description = $updateDto->description;
        $category->slug = $slug;
        $category->active = $active;

        $image_path = $this->categorySupport->uploadImage($updateDto->image, false);
        if ($image_path !== '') {
            $category->image = $image_path;
        }

        return $this->categoryRepository->save($category);
    }
}
