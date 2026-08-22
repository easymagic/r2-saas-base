<?php
namespace Category\Business\Usecases;

use Category\Data\CategoryRepositoryInterface;

class FetchForFrontendService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function query(array $filters = [])
    {
        $filters['active'] = 1;
        return $this->categoryRepository->query($filters);
    }
}
