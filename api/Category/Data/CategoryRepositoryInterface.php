<?php

namespace Category\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<CategoryEntity>
 */
interface CategoryRepositoryInterface extends AbstractBaseRepositoryInterface
{

    /**
     * @return QueryObject<CategoryEntity>
     */
    public function query(array $filters = []);
}
