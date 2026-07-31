<?php 
namespace Application\Notifications;

interface NotificationServiceInterface
{
    public function create(int $userId, string $title, string $message);
    public function myNotifications(int $userId);
    public function markAsRead(int $notificationId, int $userId);
    public function markAsUnread(int $notificationId, int $userId);
    public function delete(int $notificationId, int $userId);
    public function count(int $userId);
}