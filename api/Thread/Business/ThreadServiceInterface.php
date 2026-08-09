<?php

namespace Thread\Business;

use Shared\AbstractBaseServiceInterface;
use Thread\Data\ThreadEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<ThreadEntity>
 */
interface ThreadServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * Create a thread
     * @param int $order_id
     * @param int $sender_id
     * @param string $message
     * @param array $attachment_url
     * @return ThreadEntity
     */
    public function createThread(int $order_id, int $sender_id, string $message, array $attachment_url = []);

    /**
     * Get the thread list for an order
     * @param int $order_id
     * @param array $filters
     * @return QueryObject
     */
    public function getThreadListForOrder(int $order_id, array $filters = []);
}
