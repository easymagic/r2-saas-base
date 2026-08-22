<?php
namespace PlatformConfig\Business\Usecases;

use Shared\Contracts;
use PlatformConfig\Data\PlatformConfigRepositoryInterface;

class DeleteService
{
    private PlatformConfigRepositoryInterface $platformConfigRepository;

    public function __construct(PlatformConfigRepositoryInterface $platformConfigRepository)
    {
        $this->platformConfigRepository = $platformConfigRepository;
    }

    public function execute(int $id)
    {
        Contracts::requires($id > 0, 'Platform config ID is required');
        return $this->platformConfigRepository->delete($id);
    }
}
