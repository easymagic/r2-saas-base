<?php
namespace Category\Business\Usecases;

use Category\Data\CategoryRepositoryInterface;

class FetchForAdminService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function query(array $filters = [])
    {
        return $this->categoryRepository->query($filters);
    }
}
