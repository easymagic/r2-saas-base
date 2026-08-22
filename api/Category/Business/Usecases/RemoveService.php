<?php
namespace Category\Business\Usecases;

use Exception;
use Category\Data\CategoryRepositoryInterface;

class RemoveService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function execute(int $id)
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
}
