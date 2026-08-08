<?php

namespace SnappyOrder\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<SnappyOrderEntity>
 */
interface SnappyOrderRepositoryInterface extends AbstractBaseRepositoryInterface
{
    const ALLOWED_STATUSES = [
        'pending',
        'paid',
        'assigned',
        'completed',
        'cancelled',
    ];

    /**
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function query(array $filters = []);
}
