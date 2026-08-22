<?php

namespace PlatformConfig\Data;

use Shared\AbstractBaseRepository;
use PlatformConfig\Data\PlatformConfigEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Shared\Query\QueryObject;

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
        $this->addFilter('setting_key', function ($value, string &$sql, array &$params) {
            $sql .= " AND setting_key = :setting_key";
            $params['setting_key'] = $value;
        });
    }

    public function query(array $filters)
    {
        $this->sql = "SELECT * FROM platform_configs WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        return new QueryObject(
            $this->sql,
            $this->params,
            $this->db,
            $this->hydrateClass
        );
    }
}
