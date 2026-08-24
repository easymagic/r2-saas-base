<?php

namespace Presentation\Http\Controllers\Web;

use Category\Business\Dtos\CreateDto;
use Category\Business\Dtos\UpdateDto;
use Category\Business\Usecases\CreateService;
use Category\Business\Usecases\FetchForAdminService;
use Category\Business\Usecases\FindByIdService;
use Category\Business\Usecases\RemoveService;
use Category\Business\Usecases\UpdateService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class CategoryWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private FetchForAdminService $fetchForAdminService;
    private FindByIdService $findByIdService;
    private CreateService $createService;
    private UpdateService $updateService;
    private RemoveService $removeService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        FetchForAdminService $fetchForAdminService,
        FindByIdService $findByIdService,
        CreateService $createService,
        UpdateService $updateService,
        RemoveService $removeService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->fetchForAdminService = $fetchForAdminService;
        $this->findByIdService = $findByIdService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->removeService = $removeService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    private function adminLayout($view, $data)
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render($view, array_merge([
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ], $data));
    }

    private function categoriesList()
    {
        $categories = $this->fetchForAdminService->query([])->fetchAll();
        return is_array($categories) ? $categories : [];
    }

    public function index()
    {
        $this->adminLayout('admin/categories', [
            'title' => 'Categories',
            'subtitle' => 'Product categories',
            'nav' => 'admin-categories',
            'categories' => $this->categoriesList(),
        ]);
    }

    public function store()
    {
        try {
            $this->createService->execute(new CreateDto(
                (string) $this->request->get('name'),
                (int) $this->request->get('parent_id', 0),
                (string) $this->request->get('description'),
                (array) $this->request->get('image', []),
                (string) $this->request->get('slug', '')
            ));
            WebSession::flash('success', 'Category created.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/categories');
    }

    public function editForm()
    {
        try {
            $category = $this->findByIdService->query((int) $this->request->get('category_id'));
            $this->adminLayout('admin/category-edit', [
                'title' => 'Edit category',
                'subtitle' => $category->name,
                'nav' => 'admin-categories',
                'category' => $category,
                'categories' => $this->categoriesList(),
            ]);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/categories');
        }
    }

    public function update()
    {
        $categoryId = (int) $this->request->get('category_id');
        try {
            $this->updateService->execute(new UpdateDto(
                $categoryId,
                (string) $this->request->get('name'),
                (int) $this->request->get('parent_id', 0),
                (string) $this->request->get('description'),
                (array) $this->request->get('image', []),
                (string) $this->request->get('slug', ''),
                (int) $this->request->get('active', 1)
            ));
            WebSession::flash('success', 'Category updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/categories/' . $categoryId . '/edit');
    }

    public function delete()
    {
        try {
            $this->removeService->execute((int) $this->request->get('category_id'));
            WebSession::flash('success', 'Category deleted.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/categories');
    }

}
