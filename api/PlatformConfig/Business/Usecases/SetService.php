<?php
namespace PlatformConfig\Business\Usecases;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Data\PlatformConfigEntity;
use PlatformConfig\Data\PlatformConfigRepositoryInterface;

class SetService
{
    private PlatformConfigRepositoryInterface $platformConfigRepository;

    public function __construct(PlatformConfigRepositoryInterface $platformConfigRepository)
    {
        $this->platformConfigRepository = $platformConfigRepository;
    }

    public function execute(SetDto $setDto)
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
}
