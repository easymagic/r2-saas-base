<?php

namespace ProxyOrderChangeLog\Business;

use Shared\AbstractBaseServiceInterface;
use ProxyOrderChangeLog\Business\Dtos\LogDto;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogEntity;

/**
 * @extends AbstractBaseServiceInterface<ProxyOrderChangeLogEntity>
 */
interface ProxyOrderChangeLogServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param LogDto $logDto
     * @return ProxyOrderChangeLogEntity
     */
    public function log(LogDto $logDto);
}
