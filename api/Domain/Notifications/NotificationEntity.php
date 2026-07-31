<?php 
namespace Domain\Notifications;

class NotificationEntity
{
    public int $id;
    public int $user_id;
    public string $title;
    public string $message;
    public string $created_at;
    public string $updated_at;
}