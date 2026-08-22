<?php
namespace PlatformConfig\Presentation;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\Usecases\DeleteService;
use PlatformConfig\Business\Usecases\GetAllService;
use PlatformConfig\Business\Usecases\MigrateService;
use PlatformConfig\Business\Usecases\SetService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class PlatformConfigController
{
    private JsonResponseServiceInterface $jsonResponseService;
    private GetAllService $getAllService;
    private SetService $setService;
    private DeleteService $deleteService;
    private MigrateService $migrateService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        JsonResponseServiceInterface $jsonResponseService,
        Request $request,
        GetAllService $getAllService,
        SetService $setService,
        DeleteService $deleteService,
        MigrateService $migrateService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->jsonResponseService = $jsonResponseService;
        $this->request = $request;
        $this->getAllService = $getAllService;
        $this->setService = $setService;
        $this->deleteService = $deleteService;
        $this->migrateService = $migrateService;
        $this->apiCredentialService = $apiCredentialService;
    }

    function all() {
        $platformConfigs = $this->getAllService->query();
        return $this->jsonResponseService->success([
            'platform_configs' => $platformConfigs
        ]);
    }

    function update(){
      $setting_name =  $this->request->get('setting_name');
      $setting_value =  $this->request->get('setting_value');
      $result = $this->setService->execute(new SetDto(
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
      $result = $this->deleteService->execute($id);
      return $this->jsonResponseService->success([
        'message' => 'Platform config deleted successfully',
        'result' => $result
      ]);
    }

    function migrate(){
      $result = $this->migrateService->execute();
      return $this->jsonResponseService->success([
        'message' => 'Platform config migrated successfully',
        'result' => $result
      ]);
    }

}
