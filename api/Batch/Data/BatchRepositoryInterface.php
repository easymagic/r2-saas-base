<?php

namespace Batch\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<BatchEntity>
 */
interface BatchRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * Query the database
     * @param array $filters
     * @return QueryObject
     */
    public function query(array $filters = []);
}
