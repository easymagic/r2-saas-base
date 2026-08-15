<?php

namespace Notification\Business;

use App\Shared\Contracts\Contracts;
use Notification\Data\NotificationMigrationRepositoryInterface;
use Notification\Data\NotificationRepositoryInterface;
use Exception;
use Log\Business\LogServiceInterface;
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

    private LogServiceInterface $logService;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        NotificationMigrationRepositoryInterface $notificationMigrationRepository,
        LogServiceInterface $logService
    ) {
        parent::__construct($notificationRepository);
        $this->notificationRepository = $notificationRepository;
        $this->notificationMigrationRepository = $notificationMigrationRepository;
        $this->logService = $logService;
    }

    public function create(int $userId, string $title, string $message)
    {
        Contracts::requiresNotNullOrEmpty($userId, 'User ID');
        Contracts::requiresNotNullOrEmpty($title, 'Title');
        Contracts::requiresNotNullOrEmpty($message, 'Message');
        
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

    public function myNotifications(int $userId)
    {
        return $this->notificationRepository->query([
            "user_id" => $userId,
        ]);
    }

    public function markAsRead(int $notificationId, int $userId)
    {
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->user_id !== $userId) {
            $this->logService->createLog('notification_mark_as_read', json_encode([
                "notification" => $notification,
                "user_id" => $userId,
            ]), json_encode([
                "status" => "error",
                "message" => 'You are not authorized to mark this notification as read',
            ]), 'error');
            throw new Exception('You are not authorized to mark this notification as read');
        }
        $notification = $this->notificationRepository->find($notificationId);
        return $this->notificationRepository->save($notification->id, [
            "read_at" => date('Y-m-d H:i:s'),
            "is_read" => 1,
        ]);
    }

    public function markAsUnread(int $notificationId, int $userId)
    {
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->user_id !== $userId) {
            $this->logService->createLog('notification_mark_as_unread', json_encode([
                "notification" => $notification,
                "user_id" => $userId,
            ]), json_encode([
                "status" => "error",
                "message" => 'You are not authorized to mark this notification as unread',
            ]), 'error');
            throw new Exception('You are not authorized to mark this notification as unread');
        }
        $notification = $this->notificationRepository->find($notificationId);
        return $this->notificationRepository->save($notification->id, [
            "is_read" => 0,
        ]);
    }

    /**
     * @param int $notificationId
     * @param int $userId
     * @return NotificationEntity
     */
    public function remove(int $notificationId, int $userId)
    {
        if (empty($notificationId)) {
            throw new Exception('Notification ID is required');
        }
        if (empty($userId)) {
            throw new Exception('User ID is required');
        }

        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->isEmpty()) {
            throw new Exception('Notification not found');
        }
        if ((int) $notification->user_id !== (int) $userId) {
            $this->logService->createLog('notification_remove', json_encode([
                "notification" => $notification,
                "user_id" => $userId,
            ]), json_encode([
                "status" => "error",
                "message" => 'You are not authorized to delete this notification',
            ]), 'error');
            throw new Exception('You are not authorized to delete this notification');
        }

        $this->notificationRepository->delete($notification->id);

        return $notification;
    }

    public function migrate()
    {
        return $this->notificationMigrationRepository->migrate();
    }
}
