<?php

namespace PlatformConfig\Data;

use Shared\AbstractBaseRepository;
use PlatformConfig\Data\PlatformConfigEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;

/**
 * Platform Config Repository
 * @extends AbstractBaseRepository<PlatformConfigEntity>
 */
class PlatformConfigRepository extends AbstractBaseRepository implements PlatformConfigRepositoryInterface
{
    protected string $table = 'platform_configs';
    protected string $sql = "SELECT * FROM platform_configs WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = PlatformConfigEntity::class;

    public function __construct(
        DbServiceInterface $dbService
    ) {
        parent::__construct($dbService);
    }

}
