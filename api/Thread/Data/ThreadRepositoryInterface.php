<?php

namespace Thread\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<ThreadEntity>
 */
interface ThreadRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * Query the database
     * @param array $filters
     * @return QueryObject
     */
    public function query(array $filters = []);
}
