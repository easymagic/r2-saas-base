<?php

namespace User\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepositoryInterface<UserEntity>
 */
interface UserRepositoryInterface extends AbstractBaseRepositoryInterface
{
    /**
     * Query the users
     * @param array $filters
     * @return QueryObject
     */
    public function query(array $filters = []);
}
