<?php
namespace PlatformConfig\Presentation;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class PlatformConfigController
{
    private JsonResponseServiceInterface $jsonResponseService;
    private PlatformConfigServiceInterface $platformConfigService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        JsonResponseServiceInterface $jsonResponseService,
        Request $request,
        PlatformConfigServiceInterface $platformConfigService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->jsonResponseService = $jsonResponseService;
        $this->request = $request;
        $this->platformConfigService = $platformConfigService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function all() {
        $platformConfigs = $this->platformConfigService->getAll();
        return $this->jsonResponseService->success([
            'platform_configs' => $platformConfigs
        ]);
    }

    function update(){
      $setting_name =  $this->request->get('setting_name');
      $setting_value =  $this->request->get('setting_value');
      $result = $this->platformConfigService->set(new SetDto(
        (string) $setting_name,
        (string) $setting_value
      ));
      return $this->jsonResponseService->success([
        'message' => 'Platform config updated successfully',
        'result' => $result
      ]);
    }

    function delete(){
      $id =  (int) $this->request->get('platform_config_id');
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
