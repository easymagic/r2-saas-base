<?php

namespace Product\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Product\Business\ProductServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class ProductController
{
    private ProductServiceInterface $productService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        ProductServiceInterface $productService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->productService = $productService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function migrate()
    {
        $result = $this->productService->migrate();
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

        $product = $this->productService->create(
            (string) $this->request->get('name', ''),
            (string) $this->request->get('description', ''),
            (float) $this->request->get('price', 0),
            (float) $this->request->get('old_price', 0),
            (int) $this->request->get('stock_qty', 0),
            (int) $this->request->get('category_id', 0),
            $userId,
            (string) $this->request->get('slug', ''),
            $this->request->get('image_1', []),
            $this->request->get('image_2', []),
            $this->request->get('image_3', []),
            $this->request->get('image_4', []),
            $this->request->get('image_5', []),
            $this->request->get('image_6', []),
            $this->request->get('image_7', [])
        );
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

        $product = $this->productService->update(
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
            $this->request->get('image_1', []),
            $this->request->get('image_2', []),
            $this->request->get('image_3', []),
            $this->request->get('image_4', []),
            $this->request->get('image_5', []),
            $this->request->get('image_6', []),
            $this->request->get('image_7', [])
        );
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product updated successfully',
        ]);
    }

    function remove()
    {
        $this->productService->remove((int) $this->request->get('product_id'));
        $this->jsonResponseService->success([
            'message' => 'Product removed successfully',
        ]);
    }

    function fetchForAdmin()
    {
        $query = $this->productService->fetchForAdmin($this->request->all());
        $this->jsonResponseService->success([
            'products' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Products fetched successfully',
        ]);
    }

    function fetchForFrontend()
    {
        $query = $this->productService->fetchForFrontend($this->request->all());
        $this->jsonResponseService->success([
            'products' => $query->fetch(),
            'count' => $query->count(),
            'message' => 'Products fetched successfully',
        ]);
    }

    function fetchForMerchant()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->productService->fetchForMerchant(
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
        $product = $this->productService->findById((int) $this->request->get('product_id'));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }

    function findBySlug()
    {
        $product = $this->productService->findBySlug((string) $this->request->get('slug', ''));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }

    function findByUuid()
    {
        $product = $this->productService->findByUuid((string) $this->request->get('uuid', ''));
        $this->jsonResponseService->success([
            'product' => $product,
            'message' => 'Product fetched successfully',
        ]);
    }
}
