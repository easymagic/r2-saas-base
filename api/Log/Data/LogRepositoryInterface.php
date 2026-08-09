<?php

namespace Log\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<LogEntity>
 */
interface LogRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * @param array $filters
     * @return QueryObject<LogEntity>
     */
    public function query(array $filters = []);
}
