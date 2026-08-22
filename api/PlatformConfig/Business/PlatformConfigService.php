<?php

namespace PlatformConfig\Business;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Data\PlatformConfigMigrationRepositoryInterface;
use PlatformConfig\Data\PlatformConfigRepositoryInterface;
use Shared\AbstractBaseService;
use PlatformConfig\Data\PlatformConfigEntity;

/**
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

    function set(SetDto $setDto)
    {
        $key = strtoupper($setDto->setting);
        $platformConfig = $this->platformConfigRepository->query([
            'setting_key' => $key,
        ])->fetchOne();

        if ($platformConfig->isEmpty()) {
            return $this->platformConfigRepository->save(new PlatformConfigEntity([
                'setting_key' => $key,
                'setting_value' => $setDto->value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));
        }

        $platformConfig->setting_value = $setDto->value;
        $platformConfig->updated_at = date('Y-m-d H:i:s');
        return $this->platformConfigRepository->save($platformConfig);
    }

    function getAll()
    {
        return $this->platformConfigRepository->query([])->fetchAll();
    }

    function migrate()
    {
        return $this->platformConfigMigrationRepository->migrate();
    }
}
