<?php

namespace Infrastructure\PlatformConfig;

use Domain\PlatformConfig\PlatformConfigEntity;
use Domain\PlatformConfig\PlatformConfigRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;

class PlatformConfigRepository implements PlatformConfigRepositoryInterface
{
    private DbServiceInterface $dbService;
    private QueryBuilderServiceInterface $queryBuilderService;

    public function __construct(
        DbServiceInterface $dbService,
        QueryBuilderServiceInterface $queryBuilderService
    ) {
        $this->dbService = $dbService;
        $this->queryBuilderService = $queryBuilderService;

        $this->queryBuilderService->setSql("SELECT * FROM platform_configs WHERE 1=1 ");
    }

    /**
     * Find a platform config setting by id
     * @param int $id
     * @return PlatformConfigEntity
     */
    public function find(int $id)
    {
        $this->queryBuilderService->appendSql(" AND id = :id");
        $this->queryBuilderService->appendParams(['id' => $id]);
        $result = $this->dbService->fetchOne(
            $this->queryBuilderService->getSql(),
            $this->queryBuilderService->getParams()
        );
        return $this->hydrate($result);
    }

    private function hydrate(array $data)
    {
        return new PlatformConfigEntity($data);
    }

    /**
     * Find a platform config setting by setting
     * @param string $setting
     * @return PlatformConfigEntity
     */
    public function findBySetting(string $setting)
    {
        $this->queryBuilderService->appendSql("AND setting_key = :setting");
        $this->queryBuilderService->appendParams(['setting' => $setting]);
        $result = $this->dbService->fetchOne(
            $this->queryBuilderService->getSql(),
            $this->queryBuilderService->getParams()
        );
        return $this->hydrate($result);
    }

    /**
     * Save a platform config setting
     * @param int $id
     * @param array $data
     * @return PlatformConfigEntity
     */
    public function save(int $id, array $data)
    {
        if ($id == 0) {
            $id = $this->dbService->insert('platform_configs', $data);
        } else {
            $this->dbService->update('platform_configs', $data, ['id' => $id]);
        }
        return $this->find($id);
    }

    public function delete(int $id)
    {
        $this->dbService->delete('platform_configs', ['id' => $id]);
        return true;
    }

    public function fetchAll()
    {
        $result = $this->dbService->fetchAll(
            $this->queryBuilderService->getSql(),
            $this->queryBuilderService->getParams()
        );
        return array_map([$this, 'hydrate'], $result);
    }
}
