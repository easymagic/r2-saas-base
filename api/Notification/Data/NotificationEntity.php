<?php 
namespace Notification\Data;

use Shared\AbstractBaseEntity;

class NotificationEntity extends AbstractBaseEntity
{
    public int $id = 0;
    public int $user_id = 0;
    public string $title = '';
    public string $message = '';
    public string $read_at = '';
    public int $is_read = 0;
    public string $created_at = '';
    public string $updated_at = '';

}