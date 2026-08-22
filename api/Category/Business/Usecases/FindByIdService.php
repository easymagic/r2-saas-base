<?php
namespace Category\Business\Usecases;

use Exception;
use Category\Data\CategoryRepositoryInterface;

class FindByIdService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function query(int $id)
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
}
