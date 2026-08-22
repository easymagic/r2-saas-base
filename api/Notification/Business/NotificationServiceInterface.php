<?php

namespace Notification\Business;

use Shared\AbstractBaseServiceInterface;
use Notification\Data\NotificationEntity;
use Shared\Query\QueryObject;
use Notification\Business\Dtos\CreateDto;
use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Business\Dtos\RemoveDto;

/**
 * Notification Service Interface
 * @extends AbstractBaseServiceInterface<NotificationEntity>
 */
interface NotificationServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * Create a new notification
     * @param CreateDto $createDto
     * @return NotificationEntity
     */
    public function create(CreateDto $createDto);

    /**
     * Get all notifications for a user
     * @param MyNotificationsDto $myNotificationsDto
     * @return QueryObject
     */
    public function myNotifications(MyNotificationsDto $myNotificationsDto);

    /**
     * Mark a notification as read
     * @param MarkAsReadDto $markAsReadDto
     * @return NotificationEntity
     */
    public function markAsRead(MarkAsReadDto $markAsReadDto);

    /**
     * Mark a notification as unread
     * @param MarkAsUnreadDto $markAsUnreadDto
     * @return NotificationEntity
     */
    public function markAsUnread(MarkAsUnreadDto $markAsUnreadDto);

    /**
     * Remove a notification
     * @param RemoveDto $removeDto
     * @return NotificationEntity
     */
    public function remove(RemoveDto $removeDto);
}
