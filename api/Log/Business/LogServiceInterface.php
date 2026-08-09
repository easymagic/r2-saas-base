<?php

namespace Log\Business;

use Shared\AbstractBaseServiceInterface;
use Shared\Query\QueryObject;
use Log\Data\LogEntity;

/**
 * @extends AbstractBaseServiceInterface<LogEntity>
 */
interface LogServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param array $filters Supported: type (success|error), search, id
     * @return QueryObject<LogEntity>
     */
    public function fetchLogs(array $filters = []);

    public function createLog(string $title, string $payload, string $response, string $type);
}
