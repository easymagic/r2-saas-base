<?php
namespace PlatformConfig\Business\Usecases;

use PlatformConfig\Data\PlatformConfigRepositoryInterface;

class GetService
{
    private PlatformConfigRepositoryInterface $platformConfigRepository;

    public function __construct(PlatformConfigRepositoryInterface $platformConfigRepository)
    {
        $this->platformConfigRepository = $platformConfigRepository;
    }

    public function query(string $setting, $default = null)
    {
        $platformConfig = $this->platformConfigRepository->query([
            'setting_key' => strtoupper($setting),
        ])->fetchOne();

        if ($platformConfig->isEmpty()) {
            return $default;
        }

        return $platformConfig->setting_value;
    }
}
