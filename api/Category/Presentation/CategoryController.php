<?php

namespace Category\Presentation;

use Category\Business\Dtos\CreateDto;
use Category\Business\Dtos\UpdateDto;
use Category\Business\Usecases\CreateService;
use Category\Business\Usecases\FetchForAdminService;
use Category\Business\Usecases\FetchForFrontendService;
use Category\Business\Usecases\FindByIdService;
use Category\Business\Usecases\FindBySlugService;
use Category\Business\Usecases\MigrateService;
use Category\Business\Usecases\RemoveService;
use Category\Business\Usecases\UpdateService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class CategoryController
{
    private MigrateService $migrateService;
    private CreateService $createService;
    private UpdateService $updateService;
    private RemoveService $removeService;
    private FetchForAdminService $fetchForAdminService;
    private FetchForFrontendService $fetchForFrontendService;
    private FindByIdService $findByIdService;
    private FindBySlugService $findBySlugService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        MigrateService $migrateService,
        CreateService $createService,
        UpdateService $updateService,
        RemoveService $removeService,
        FetchForAdminService $fetchForAdminService,
        FetchForFrontendService $fetchForFrontendService,
        FindByIdService $findByIdService,
        FindBySlugService $findBySlugService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->migrateService = $migrateService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->removeService = $removeService;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->fetchForFrontendService = $fetchForFrontendService;
        $this->findByIdService = $findByIdService;
        $this->findBySlugService = $findBySlugService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function create()
    {
        $category = $this->createService->execute(new CreateDto(
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
        $category = $this->updateService->execute(new UpdateDto(
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
        $this->removeService->execute((int) $this->request->get('category_id'));
        $this->jsonResponseService->success([
            'message' => 'Category removed successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->fetchForAdminService->query($this->request->all());
        $this->jsonResponseService->success([
            'categories' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Categories fetched successfully',
        ]);
    }

    function fetchForFrontend()
    {
        $query = $this->fetchForFrontendService->query($this->request->all());
        $this->jsonResponseService->success([
            'categories' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Categories fetched successfully',
        ]);
    }

    function findById()
    {
        $category = $this->findByIdService->query((int) $this->request->get('category_id'));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category fetched successfully',
        ]);
    }

    function findBySlug()
    {
        $category = $this->findBySlugService->query((string) $this->request->get('slug', ''));
        $this->jsonResponseService->success([
            'category' => $category,
            'message' => 'Category fetched successfully',
        ]);
    }
}
