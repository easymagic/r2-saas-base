<?php

namespace Thread\Business;

interface ThreadNotificationServiceInterface
{
    /**
     * Email the order customer about a new thread message.
     * @param int $thread_id
     * @return void
     */
    public function sendNotificationToUser(int $thread_id);
}
