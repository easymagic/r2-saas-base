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
     * Check if a user exists
     * @param int $id
     * @return bool
     */
    function idExists(int $id);
}
