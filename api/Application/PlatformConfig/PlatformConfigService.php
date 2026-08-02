<?php

namespace Application\PlatformConfig;

use Domain\PlatformConfig\PlatformConfigRepositoryInterface;
use Exception;
use Presentation\ApiCredential\ApiCredentialServiceInterface;

class PlatformConfigService implements PlatformConfigServiceInterface
{

    private PlatformConfigRepositoryInterface $platformConfigRepository;
    private PlatformConfigMigrationServiceInterface $platformConfigMigrationService;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        PlatformConfigRepositoryInterface $platformConfigRepository,
        PlatformConfigMigrationServiceInterface $platformConfigMigrationService,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->platformConfigRepository = $platformConfigRepository;
        $this->platformConfigMigrationService = $platformConfigMigrationService;
        $this->apiCredentialService = $apiCredentialService;
    }

    /**
     * Get a platform config setting
     * @param string $setting
     * @return string
     */
    function get(string $setting, mixed $default = null)
    {
        $setting = strtoupper($setting);

        $platformConfig = $this->platformConfigRepository->findBySetting($setting);

        if ($platformConfig->isEmpty()) {
            return $default;
        }

        return $platformConfig->setting_value;
    }

    /**
     * Set a platform config setting
     * @param string $setting
     * @param string $value
     * @return void
     */
    function set(string $setting, string $value)
    {
        $platformConfig = $this->platformConfigRepository->findBySetting($setting);

        if ($platformConfig->isEmpty()) {
            $platformConfig = $this->platformConfigRepository->save(0, [
                'setting_key' => strtoupper($setting),
                'setting_value' => $value,
            ]);
        }

        $platformConfig = $this->platformConfigRepository->save($platformConfig->id, [
            'setting_value' => $value,
        ]);

        return $platformConfig;
    }

    /**
     * Get all platform config settings
     * @return array
     */
    function getAll()
    {
        return $this->platformConfigRepository->fetchAll();
    }

    /**
     * Migrate the platform config settings
     * @return void
     */
    function migrate()
    {
        return $this->platformConfigMigrationService->migrate();
    }

    /**
     * Delete a platform config setting
     * @param int $id
     * @return bool
     */
    function delete(int $id)
    {
        $user = $this->apiCredentialService->getAuthUser();
        if (!$user->isAdmin()){
            throw new Exception('You are not authorized to delete this platform config');
        }
        return $this->platformConfigRepository->delete($id);
    }
}
