<?php
namespace Category\Business\Usecases;

use Exception;
use Category\Data\CategoryRepositoryInterface;

class FindBySlugService
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function query(string $slug)
    {
        $slug = trim($slug);
        if ($slug === '') {
            throw new Exception('Slug is required');
        }

        $category = $this->categoryRepository->findBy('slug', $slug);
        if ($category->isEmpty()) {
            throw new Exception('Category not found');
        }

        return $category;
    }
}
