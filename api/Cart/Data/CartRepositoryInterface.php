<?php

namespace Cart\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<CartEntity>
 */
interface CartRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * Query the cart repository
     * @param array $filters
     * @return QueryObject
     */
    public function query(array $filters = []);
}
