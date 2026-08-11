<?php

namespace UserKyc\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<UserKycEntity>
 */
interface UserKycRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * @param array $filters
     * @return QueryObject<UserKycEntity>
     */
    public function query(array $filters = []);
}
