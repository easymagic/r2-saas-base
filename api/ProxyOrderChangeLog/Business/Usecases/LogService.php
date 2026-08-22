<?php
namespace ProxyOrderChangeLog\Business\Usecases;

use ProxyOrderChangeLog\Business\Dtos\LogDto;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogRepositoryInterface;

class LogService
{
    private ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository;

    public function __construct(ProxyOrderChangeLogRepositoryInterface $proxyOrderChangeLogRepository)
    {
        $this->proxyOrderChangeLogRepository = $proxyOrderChangeLogRepository;
    }

    public function execute(LogDto $logDto)
    {
        return $this->proxyOrderChangeLogRepository->save(new ProxyOrderChangeLogEntity([
            'snappy_order_id' => $logDto->order_id,
            'field_name' => $logDto->field_name,
            'old_value' => $logDto->old_value,
            'new_value' => $logDto->new_value,
        ]));
    }
}
