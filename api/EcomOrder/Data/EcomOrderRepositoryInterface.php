<?php

namespace EcomOrder\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<EcomOrderEntity>
 */
interface EcomOrderRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * @param array $filters
     * @return QueryObject
     */
    public function query(array $filters = []);
}
