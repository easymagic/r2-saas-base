<?php

namespace Thread\Business;

use Shared\AbstractBaseServiceInterface;
use Thread\Business\Dtos\CreateThreadDto;
use Thread\Data\ThreadEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<ThreadEntity>
 */
interface ThreadServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param CreateThreadDto $createThreadDto
     * @return ThreadEntity
     */
    public function createThread(CreateThreadDto $createThreadDto);

    /**
     * @param int $order_id
     * @param array $filters
     * @return QueryObject
     */
    public function getThreadListForOrder(int $order_id, array $filters = []);
}
