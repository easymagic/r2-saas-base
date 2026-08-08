<?php

namespace ProxyOrderChangeLog\Business;

use Shared\AbstractBaseService;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogRepositoryInterface;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<ProxyOrderChangeLogEntity, ProxyOrderChangeLogRepositoryInterface>
 */
class ProxyOrderChangeLogService extends AbstractBaseService implements ProxyOrderChangeLogServiceInterface
{
    private ProxyOrderChangeLogMigrationRepositoryInterface $proxyOrderChangeLogMigrationRepositoryInterface;
    private ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository;

    public function __construct(
        ProxyOrderChangeLogMigrationRepositoryInterface $proxyOrderChangeLogMigrationRepositoryInterface,
        ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository
    ) {
        parent::__construct($proxyOrderChangeLogRepository);
        $this->proxyOrderChangeLogMigrationRepositoryInterface = $proxyOrderChangeLogMigrationRepositoryInterface;
        $this->proxyOrderChangeLogRepository = $proxyOrderChangeLogRepository;
    }

    public function migrate()
    {
        return $this->proxyOrderChangeLogMigrationRepositoryInterface->migrate();
    }

    public function log(int $order_id, string $field_name, string $old_value, string $new_value)
    {
        return $this->proxyOrderChangeLogRepository->save(0, [
            'snappy_order_id' => $order_id,
            'field_name' => $field_name,
            'old_value' => $old_value,
            'new_value' => $new_value,
        ]);
    }
}
