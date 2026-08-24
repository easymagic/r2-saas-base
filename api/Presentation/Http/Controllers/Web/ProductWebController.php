<?php

namespace Presentation\Http\Controllers\Web;

use Category\Business\Usecases\FetchForAdminService as CategoryFetchForAdminService;
use Product\Business\Dtos\CreateDto;
use Product\Business\Dtos\UpdateDto;
use Product\Business\Usecases\CreateService;
use Product\Business\Usecases\FetchForAdminService;
use Product\Business\Usecases\FetchForFrontendService;
use Product\Business\Usecases\FindByIdService;
use Product\Business\Usecases\RemoveService;
use Product\Business\Usecases\UpdateService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;

class ProductWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private FetchForAdminService $fetchForAdminService;
    private FetchForFrontendService $fetchForFrontendService;
    private FindByIdService $findByIdService;
    private CreateService $createService;
    private UpdateService $updateService;
    private RemoveService $removeService;
    private CategoryFetchForAdminService $categoryFetchForAdminService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        FetchForAdminService $fetchForAdminService,
        FetchForFrontendService $fetchForFrontendService,
        FindByIdService $findByIdService,
        CreateService $createService,
        UpdateService $updateService,
        RemoveService $removeService,
        CategoryFetchForAdminService $categoryFetchForAdminService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->fetchForFrontendService = $fetchForFrontendService;
        $this->findByIdService = $findByIdService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->removeService = $removeService;
        $this->categoryFetchForAdminService = $categoryFetchForAdminService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    private function userLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function adminLayout($view, $data)
    {
        $this->userLayout($view, array_merge(['layout_nav' => 'admin'], $data));
    }

    private function categoriesList()
    {
        $categories = $this->categoryFetchForAdminService->query([])->fetchAll();
        return is_array($categories) ? $categories : [];
    }

    private function usersList()
    {
        $users = $this->fetchUsersAsAdminService->query([])->fetchAll();
        return is_array($users) ? $users : [];
    }

    private function categoryMap(array $categories)
    {
        $map = [];
        foreach ($categories as $category) {
            $map[(int) $category->id] = $category->name;
        }
        return $map;
    }

    public function shop()
    {
        $products = $this->fetchForFrontendService->query([])->fetchAll();
        if (!is_array($products)) {
            $products = [];
        }
        $this->userLayout('shop/index', [
            'title' => 'Shop',
            'subtitle' => 'Browse products',
            'nav' => 'shop',
            'products' => $products,
        ]);
    }

    public function shopShow()
    {
        try {
            $product = $this->findByIdService->query((int) $this->request->get('product_id'));
            if ((int) $product->active !== 1) {
                throw new \Exception('Product is not available');
            }
            $this->userLayout('shop/product', [
                'title' => $product->name,
                'subtitle' => format_naira($product->price),
                'nav' => 'shop',
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/shop');
        }
    }

    public function adminIndex()
    {
        $categories = $this->categoriesList();
        $products = $this->fetchForAdminService->query([])->fetchAll();
        if (!is_array($products)) {
            $products = [];
        }
        $this->adminLayout('admin/products', [
            'title' => 'Products',
            'subtitle' => 'Catalog management',
            'nav' => 'admin-products',
            'products' => $products,
            'categoryMap' => $this->categoryMap($categories),
        ]);
    }

    public function adminCreateForm()
    {
        $this->adminLayout('admin/product-form', [
            'title' => 'Create product',
            'subtitle' => 'Add to catalog',
            'nav' => 'admin-products',
            'product' => null,
            'categories' => $this->categoriesList(),
            'users' => $this->usersList(),
            'old' => [],
        ]);
    }

    public function adminStore()
    {
        try {
            $product = $this->createService->execute(new CreateDto(
                (string) $this->request->get('name'),
                (string) $this->request->get('description'),
                (float) $this->request->get('price', 0),
                (float) $this->request->get('old_price', 0),
                (int) $this->request->get('stock_qty', 0),
                (int) $this->request->get('category_id'),
                (int) $this->request->get('user_id'),
                (string) $this->request->get('slug', ''),
                (array) $this->request->get('image_1', []),
                (array) $this->request->get('image_2', []),
                (array) $this->request->get('image_3', []),
                (array) $this->request->get('image_4', []),
                (array) $this->request->get('image_5', []),
                (array) $this->request->get('image_6', []),
                (array) $this->request->get('image_7', [])
            ));
            WebSession::flash('success', 'Product #' . $product->id . ' created (inactive until activated).');
            WebSession::redirect('/admin/products/' . $product->id . '/edit');
        } catch (\Exception $e) {
            $this->adminLayout('admin/product-form', [
                'title' => 'Create product',
                'subtitle' => 'Add to catalog',
                'nav' => 'admin-products',
                'product' => null,
                'categories' => $this->categoriesList(),
                'users' => $this->usersList(),
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'old' => [
                    'name' => (string) $this->request->get('name', ''),
                    'description' => (string) $this->request->get('description', ''),
                    'price' => (string) $this->request->get('price', ''),
                    'old_price' => (string) $this->request->get('old_price', ''),
                    'stock_qty' => (string) $this->request->get('stock_qty', ''),
                    'category_id' => (string) $this->request->get('category_id', ''),
                    'user_id' => (string) $this->request->get('user_id', ''),
                    'slug' => (string) $this->request->get('slug', ''),
                ],
            ]);
        }
    }

    public function adminEditForm()
    {
        try {
            $product = $this->findByIdService->query((int) $this->request->get('product_id'));
            $this->adminLayout('admin/product-form', [
                'title' => 'Edit product',
                'subtitle' => $product->name,
                'nav' => 'admin-products',
                'product' => $product,
                'categories' => $this->categoriesList(),
                'users' => $this->usersList(),
                'old' => [],
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/products');
        }
    }

    public function adminUpdate()
    {
        $productId = (int) $this->request->get('product_id');
        try {
            $this->updateService->execute(new UpdateDto(
                $productId,
                (string) $this->request->get('name'),
                (string) $this->request->get('description'),
                (float) $this->request->get('price', 0),
                (float) $this->request->get('old_price', 0),
                (int) $this->request->get('stock_qty', 0),
                (int) $this->request->get('category_id'),
                (int) $this->request->get('user_id'),
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
            WebSession::flash('success', 'Product updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/products/' . $productId . '/edit');
    }

    public function adminDelete()
    {
        try {
            $this->removeService->execute((int) $this->request->get('product_id'));
            WebSession::flash('success', 'Product deleted.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/products');
    }

}
