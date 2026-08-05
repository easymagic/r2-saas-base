<?php

namespace Business\PlatformConfig;

use Data\PlatformConfig\PlatformConfigMigrationRepositoryInterface;
use Data\PlatformConfig\PlatformConfigRepositoryInterface;
use Data\User\UserRepositoryInterface;
use Exception;

class PlatformConfigService implements PlatformConfigServiceInterface
{

    private PlatformConfigRepositoryInterface $platformConfigRepository;
    private PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        PlatformConfigRepositoryInterface $platformConfigRepository,
        PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->platformConfigRepository = $platformConfigRepository;
        $this->platformConfigMigrationRepository = $platformConfigMigrationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get a platform config setting
     * @param string $setting
     * @return string
     */
    function get(string $setting, mixed $default = null)
    {
        $setting = strtoupper($setting);

        $platformConfig = $this->platformConfigRepository->findBy("setting_key", $setting);

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
        $platformConfig = $this->platformConfigRepository->findBy("setting_key", $setting);

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
        return $this->platformConfigMigrationRepository->migrate();
    }

    /**
     * Delete a platform config setting
     * @param int $id
     * @return bool
     */
    function delete(int $id, int $userId)
    {
        $user = $this->userRepository->find($userId);
        if (!$user->isAdmin()) {
            throw new Exception('You are not authorized to delete this platform config');
        }
        return $this->platformConfigRepository->delete($id);
    }
}
