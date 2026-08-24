<?php

namespace Presentation\Http\Controllers\Web;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\Usecases\DeleteService;
use PlatformConfig\Business\Usecases\GetAllService;
use PlatformConfig\Business\Usecases\SetService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class PlatformConfigWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private GetAllService $getAllService;
    private SetService $setService;
    private DeleteService $deleteService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        GetAllService $getAllService,
        SetService $setService,
        DeleteService $deleteService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->getAllService = $getAllService;
        $this->setService = $setService;
        $this->deleteService = $deleteService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render('admin/platform-config', [
            'title' => 'Platform config',
            'subtitle' => 'Application settings',
            'nav' => 'admin-platform',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'configs' => $this->getAllService->query(),
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function update()
    {
        try {
            $this->setService->execute(new SetDto(
                (string) $this->request->get('setting_name'),
                (string) $this->request->get('setting_value')
            ));
            WebSession::flash('success', 'Setting saved.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/platform-config');
    }

    public function delete()
    {
        try {
            $this->deleteService->execute((int) $this->request->get('platform_config_id'));
            WebSession::flash('success', 'Setting deleted.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/platform-config');
    }

}
