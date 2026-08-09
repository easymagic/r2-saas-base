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

        'placed',

        'shipped-to-facility',

        'arrived-at-facility',

        'shipped-to-destination-country',

        'arrived-at-destination-country',

        'arrived-at-destination-facility',

        'ready-for-pickup',

        'delivered',

        'cancelled',

    ];
    /**
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function query(array $filters = []);
}
