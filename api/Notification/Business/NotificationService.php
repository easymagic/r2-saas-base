<?php

namespace Notification\Business;

use Notification\Data\NotificationMigrationRepositoryInterface;
use Notification\Data\NotificationRepositoryInterface;
use Exception;
use Shared\AbstractBaseService;
use Notification\Data\NotificationEntity;

/**
 * Notification Service
 * @extends AbstractBaseService<NotificationEntity,NotificationRepositoryInterface>
 */
class NotificationService extends AbstractBaseService implements NotificationServiceInterface
{

    private NotificationRepositoryInterface $notificationRepository;

    private NotificationMigrationRepositoryInterface $notificationMigrationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository, NotificationMigrationRepositoryInterface $notificationMigrationRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->notificationMigrationRepository = $notificationMigrationRepository;
    }

    public function create(int $userId, string $title, string $message) {
        if (empty($userId)){
            throw new \Exception('User ID is required');
        }
        if (empty($title)){
            throw new \Exception('Title is required');
        }
        if (empty($message)){
            throw new Exception('Message is required');
        }
        $notification = $this->notificationRepository->save(0, [
            "user_id" => $userId,
            "title" => $title,
            "message" => $message,
            "is_read" => 0,
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
        ]);
        return $notification;
    }

    public function myNotifications(int $userId) {
      return $this->notificationRepository->filterBy("user_id", $userId)->fetch();
    }

    public function markAsRead(int $notificationId, int $userId) {
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->user_id !== $userId) {
            throw new Exception('You are not authorized to mark this notification as read');
        }
        $notification = $this->notificationRepository->find($notificationId);
        return $this->notificationRepository->save($notification->id, [
            "read_at" => date('Y-m-d H:i:s'),
            "is_read" => 1,
        ]);
    }

    public function markAsUnread(int $notificationId, int $userId) {
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->user_id !== $userId) {
            throw new Exception('You are not authorized to mark this notification as unread');
        }
        $notification = $this->notificationRepository->find($notificationId);
        return$this->notificationRepository->save($notification->id, [
            "is_read" => 0,
        ]);
    }


    public function migrate() {
        return $this->notificationMigrationRepository->migrate();
    }
}
