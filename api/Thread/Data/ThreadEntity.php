<?php

namespace Thread\Data;

use Shared\AbstractBaseEntity;

class ThreadEntity extends AbstractBaseEntity
{
    public int $order_id = 0;
    public int $sender_id = 0;
    public string $message = '';
    public string $created_at = '';
    public string $attachment_url = '';
}
