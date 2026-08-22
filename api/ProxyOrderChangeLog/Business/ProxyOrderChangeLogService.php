<?php

namespace ProxyOrderChangeLog\Business;

use Shared\AbstractBaseService;
use ProxyOrderChangeLog\Business\Dtos\LogDto;
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

    public function log(LogDto $logDto)
    {
        return $this->proxyOrderChangeLogRepository->save(new ProxyOrderChangeLogEntity([
            'snappy_order_id' => $logDto->order_id,
            'field_name' => $logDto->field_name,
            'old_value' => $logDto->old_value,
            'new_value' => $logDto->new_value,
        ]));
    }
}
