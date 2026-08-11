<?php

namespace Product\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<ProductEntity>
 */
interface ProductRepositoryInterface extends AbstractBaseRepositoryInterface
{

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function query(array $filters = []);
}
