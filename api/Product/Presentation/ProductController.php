<?php

namespace Product\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Product\Business\Dtos\CreateDto;
use Product\Business\Dtos\UpdateDto;
use Product\Business\Usecases\CreateService;
use Product\Business\Usecases\FetchForAdminService;
use Product\Business\Usecases\FetchForFrontendService;
use Product\Business\Usecases\FetchForMerchantService;
use Product\Business\Usecases\FindByIdService;
use Product\Business\Usecases\FindBySlugService;
use Product\Business\Usecases\FindByUuidService;
use Product\Business\Usecases\MigrateService;
use Product\Business\Usecases\RemoveService;
use Product\Business\Usecases\UpdateService;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class ProductController
{
    private MigrateService $migrateService;
    private CreateService $createService;
    private UpdateService $updateService;
    private RemoveService $removeService;
    private FetchForAdminService $fetchForAdminService;
    private FetchForFrontendService $fetchForFrontendService;
    private FetchForMerchantService $fetchForMerchantService;
    private FindByIdService $findByIdService;
    private FindBySlugService $findBySlugService;
    private FindByUuidService $findByUuidService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        MigrateService $migrateService,
        CreateService $createService,
        UpdateService $updateService,
        RemoveService $removeService,
        FetchForAdminService $fetchForAdminService,
        FetchForFrontendService $fetchForFrontendService,
        FetchForMerchantService $fetchForMerchantService,
        FindByIdService $findByIdService,
        FindBySlugService $findBySlugService,
        FindByUuidService $findByUuidService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->migrateService = $migrateService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->removeService = $removeService;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->fetchForFrontendService = $fetchForFrontendService;
        $this->fetchForMerchantService = $fetchForMerchantService;
        $this->findByIdService = $findByIdService;
        $this->findBySlugService = $findBySlugService;
        $this->findByUuidService = $findByUuidService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
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
        $user = $this->apiCredentialService->getAuthUser();
        $userId = (int) $this->request->get('user_id', $user->id);
        if ($userId <= 0) {
            $userId = (int) $user->id;
        }

        $product = $this->createService->execute(new CreateDto(
            (string) $this->request->get('name', ''),
            (string) $this->request->get('description', ''),
            (float) $this->request->get('price', 0),
            (float) $this->request->get('old_price', 0),
            (int) $this->request->get('stock_qty', 0),
            (int) $this->request->get('category_id', 0),
            $userId,
            (string) $this->request->get('slug', ''),
            (array) $this->request->get('image_1', []),
            (array) $this->request->get('image_2', []),
            (array) $this->request->get('image_3', []),
            (array) $this->request->get('image_4', []),
            (array) $this->request->get('image_5', []),
            (array) $this->request->get('image_6', []),
            (array) $this->request->get('image_7', [])
        ));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product created successfully',
        ]);
    }

    function update()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = (int) $this->request->get('user_id', $user->id);
        if ($userId <= 0) {
            $userId = (int) $user->id;
        }

        $product = $this->updateService->execute(new UpdateDto(
            (int) $this->request->get('product_id'),
            (string) $this->request->get('name', ''),
            (string) $this->request->get('description', ''),
            (float) $this->request->get('price', 0),
            (float) $this->request->get('old_price', 0),
            (int) $this->request->get('stock_qty', 0),
            (int) $this->request->get('category_id', 0),
            $userId,
            (string) $this->request->get('slug', ''),
            (int) $this->request->get('active', 0),
            (array) $this->request->get('image_1', []),
            (array) $this->request->get('image_2', []),
            (array) $this->request->get('image_3', []),
            (array) $this->request->get('image_4', []),
            (array) $this->request->get('image_5', []),
            (array) $this->request->get('image_6', []),
            (array) $this->request->get('image_7', [])
        ));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product updated successfully',
        ]);
    }

    function remove()
    {
        $this->removeService->execute((int) $this->request->get('product_id'));
        $this->jsonResponseService->success([
            'message' => 'Product removed successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->fetchForAdminService->query($this->request->all());
        $this->jsonResponseService->success([
            'products' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Products fetched successfully',
        ]);
    }

    function fetchForFrontend()
    {
        $query = $this->fetchForFrontendService->query($this->request->all());
        $this->jsonResponseService->success([
            'products' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Products fetched successfully',
        ]);
    }

    function fetchForMerchant()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->fetchForMerchantService->query(
            (int) $user->id,
            $this->request->all()
        );
        $this->jsonResponseService->success([
            'products' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Products fetched successfully',
        ]);
    }

    function findById()
    {
        $product = $this->findByIdService->query((int) $this->request->get('product_id'));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }

    function findBySlug()
    {
        $product = $this->findBySlugService->query((string) $this->request->get('slug', ''));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }

    function findByUuid()
    {
        $product = $this->findByUuidService->query((string) $this->request->get('uuid', ''));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }
}
