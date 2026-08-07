<?php 
namespace Notification\Business;

use Shared\AbstractBaseServiceInterface;
use Notification\Data\NotificationEntity;

/**
 * Notification Service Interface
 * @extends AbstractBaseServiceInterface<NotificationEntity>
 */
interface NotificationServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * Create a new notification
     * @param int $userId
     * @param string $title
     * @param string $message
     * @return NotificationEntity
     */
    public function create(int $userId, string $title, string $message);

    /**
     * Get all notifications for a user
     * @param int $userId
     * @return NotificationEntity[]
     */
    public function myNotifications(int $userId);

    /**
     * Mark a notification as read
     * @param int $notificationId
     * @param int $userId
     * @return NotificationEntity
     */
    public function markAsRead(int $notificationId, int $userId);

    
    /**
     * Mark a notification as unread
     * @param int $notificationId
     * @param int $userId
     * @return NotificationEntity
     */
    public function markAsUnread(int $notificationId, int $userId);

}