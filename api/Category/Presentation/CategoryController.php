<?php

namespace Category\Presentation;

use Category\Business\Dtos\CreateDto;
use Category\Business\Dtos\UpdateDto;
use Category\Business\CategoryServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class CategoryController
{
    private CategoryServiceInterface $categoryService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        CategoryServiceInterface $categoryService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->categoryService = $categoryService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->categoryService->migrate();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $category = $this->categoryService->create(new CreateDto(
            (string) $this->request->get('name', ''),
            (int) $this->request->get('parent_id', 0),
            (string) $this->request->get('description', ''),
            (array) $this->request->get('image', []),
            (string) $this->request->get('slug', '')
        ));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category created successfully',
        ]);
    }

    function update()
    {
        $category = $this->categoryService->update(new UpdateDto(
            (int) $this->request->get('category_id'),
            (string) $this->request->get('name', ''),
            (int) $this->request->get('parent_id', 0),
            (string) $this->request->get('description', ''),
            (array) $this->request->get('image', []),
            (string) $this->request->get('slug', ''),
            (int) $this->request->get('active', 0)
        ));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category updated successfully',
        ]);
    }

    function remove()
    {
        $this->categoryService->remove((int) $this->request->get('category_id'));
        $this->jsonResponseService->success([
            'message' => 'Category removed successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->categoryService->fetchForAdmin($this->request->all());
        $this->jsonResponseService->success([
            'categories' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Categories fetched successfully',
        ]);
    }

    function fetchForFrontend()
    {
        $query = $this->categoryService->fetchForFrontend($this->request->all());
        $this->jsonResponseService->success([
            'categories' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Categories fetched successfully',
        ]);
    }

    function findById()
    {
        $category = $this->categoryService->findById((int) $this->request->get('category_id'));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category fetched successfully',
        ]);
    }

    function findBySlug()
    {
        $category = $this->categoryService->findBySlug((string) $this->request->get('slug', ''));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category fetched successfully',
        ]);
    }
}
