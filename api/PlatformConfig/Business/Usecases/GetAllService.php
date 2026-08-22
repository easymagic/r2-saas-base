<?php
namespace PlatformConfig\Business\Usecases;

use PlatformConfig\Data\PlatformConfigRepositoryInterface;

class GetAllService
{
    private PlatformConfigRepositoryInterface $platformConfigRepository;

    public function __construct(PlatformConfigRepositoryInterface $platformConfigRepository)
    {
        $this->platformConfigRepository = $platformConfigRepository;
    }

    public function query()
    {
        return $this->platformConfigRepository->query([])->fetchAll();
    }
}
