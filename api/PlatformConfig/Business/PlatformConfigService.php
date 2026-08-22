<?php

namespace PlatformConfig\Business;

use PlatformConfig\Data\PlatformConfigMigrationRepositoryInterface;
use PlatformConfig\Data\PlatformConfigRepositoryInterface;
use Shared\AbstractBaseService;
use PlatformConfig\Data\PlatformConfigEntity;

/**
 * Platform Config Service
 * @extends AbstractBaseService<PlatformConfigEntity,PlatformConfigRepositoryInterface>
 */
class PlatformConfigService extends AbstractBaseService implements PlatformConfigServiceInterface
{

    private PlatformConfigRepositoryInterface $platformConfigRepository;
    private PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository;

    public function __construct(
        PlatformConfigRepositoryInterface $platformConfigRepository,
        PlatformConfigMigrationRepositoryInterface $platformConfigMigrationRepository
    ) {
        parent::__construct($platformConfigRepository);
        $this->platformConfigRepository = $platformConfigRepository;
        $this->platformConfigMigrationRepository = $platformConfigMigrationRepository;
    }

    /**
     * Get a platform config setting
     * @param string $setting
     * @return mixed
     */
    function get(string $setting, mixed $default = null)
    {
        $platformConfig = $this->platformConfigRepository->query([
            'setting_key' => strtoupper($setting),
        ])->fetchOne();

        if ($platformConfig->isEmpty()) {
            return $default;
        }

        return $platformConfig->setting_value;
    }

    /**
     * Set a platform config setting
     * @param string $setting
     * @param string $value
     * @return PlatformConfigEntity
     */
    function set(string $setting, string $value)
    {
        $key = strtoupper($setting);
        $platformConfig = $this->platformConfigRepository->query([
            'setting_key' => $key,
        ])->fetchOne();

        if ($platformConfig->isEmpty()) {
            return $this->platformConfigRepository->save(new PlatformConfigEntity([
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }

        $platformConfig->setting_value = $value;
        $platformConfig->updated_at = date('Y-m-d H:i:s');
        return $this->platformConfigRepository->save($platformConfig);
    }

    /**
     * Get all platform config settings
     * @return array
     */
    function getAll()
    {
        return $this->platformConfigRepository->query([])->fetchAll();
    }

    /**
     * Migrate the platform config settings
     * @return mixed
     */
    function migrate()
    {
        return $this->platformConfigMigrationRepository->migrate();
    }
}
