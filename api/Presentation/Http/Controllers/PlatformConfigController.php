<?php
namespace Presentation\Http\Controllers;

use Application\PlatformConfig\PlatformConfigServiceInterface;
use Domain\PlatformConfig\PlatformConfigRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class PlatformConfigController
{
    private PlatformConfigRepositoryInterface $platformConfigRepository;
    private JsonResponseServiceInterface $jsonResponseService;
    private PlatformConfigServiceInterface $platformConfigService;
    private Request $request;

    public function __construct(
        PlatformConfigRepositoryInterface $platformConfigRepository,
        JsonResponseServiceInterface $jsonResponseService,
        Request $request,
        PlatformConfigServiceInterface $platformConfigService
    ) {
        $this->platformConfigRepository = $platformConfigRepository;
        $this->jsonResponseService = $jsonResponseService;
        $this->request = $request;
        $this->platformConfigService = $platformConfigService;
    }

    function all() {
        $platformConfigs = $this->platformConfigRepository->fetchAll();
        return $this->jsonResponseService->success([
            'platform_configs' => $platformConfigs
        ]);
    }

    function update(){
      $setting_name =  $this->request->get('setting_name');
      $setting_value =  $this->request->get('setting_value');
      $result = $this->platformConfigService->set($setting_name, $setting_value);
      return $this->jsonResponseService->success([
        'message' => 'Platform config updated successfully',
        'result' => $result
      ]);
    }

    function delete(){
      $id =  $this->request->get('platform_config_id');
      $result = $this->platformConfigService->delete($id);
      return $this->jsonResponseService->success([
        'message' => 'Platform config deleted successfully',
        'result' => $result
      ]);
    }

    function migrate(){
      $result = $this->platformConfigService->migrate();
      return $this->jsonResponseService->success([
        'message' => 'Platform config migrated successfully',
        'result' => $result
      ]);
    }

}
