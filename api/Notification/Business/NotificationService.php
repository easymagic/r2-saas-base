<?php

namespace Notification\Business;

use Shared\Contracts;
use Notification\Data\NotificationMigrationRepositoryInterface;
use Notification\Data\NotificationRepositoryInterface;
use Exception;
use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Shared\AbstractBaseService;
use Notification\Data\NotificationEntity;
use Notification\Business\Dtos\CreateDto;
use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Business\Dtos\RemoveDto;

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

    public function create(CreateDto $createDto)
    {
        $notification = $this->notificationRepository->save(new NotificationEntity([
            'user_id' => $createDto->userId,
            'title' => $createDto->title,
            'message' => $createDto->message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
        return $notification;
    }

    public function myNotifications(MyNotificationsDto $myNotificationsDto)
    {
        return $this->notificationRepository->query([
            'user_id' => $myNotificationsDto->userId,
        ]);
    }

    public function markAsRead(MarkAsReadDto $markAsReadDto)
    {
        $notification = $this->notificationRepository->find($markAsReadDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');
        if ((int) $notification->user_id !== (int) $markAsReadDto->userId) {
            $this->logService->createLog(new CreateLogDto('notification_mark_as_read', json_encode([
                'notification' => $notification,
                'user_id' => $markAsReadDto->userId,
            ]), json_encode([
                'status' => 'error',
                'message' => 'You are not authorized to mark this notification as read',
            ]), 'error'));
            throw new Exception('You are not authorized to mark this notification as read');
        }
        $notification->read_at = date('Y-m-d H:i:s');
        $notification->is_read = 1;
        $notification->updated_at = date('Y-m-d H:i:s');
        return $this->notificationRepository->save($notification);
    }

    public function markAsUnread(MarkAsUnreadDto $markAsUnreadDto)
    {
        $notification = $this->notificationRepository->find($markAsUnreadDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');
        if ((int) $notification->user_id !== (int) $markAsUnreadDto->userId) {
            $this->logService->createLog(new CreateLogDto('notification_mark_as_unread', json_encode([
                'notification' => $notification,
                'user_id' => $markAsUnreadDto->userId,
            ]), json_encode([
                'status' => 'error',
                'message' => 'You are not authorized to mark this notification as unread',
            ]), 'error'));
            throw new Exception('You are not authorized to mark this notification as unread');
        }
        $notification->is_read = 0;
        $notification->updated_at = date('Y-m-d H:i:s');
        return $this->notificationRepository->save($notification);
    }

    public function remove(RemoveDto $removeDto)
    {
        $notification = $this->notificationRepository->find($removeDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');
        if ((int) $notification->user_id !== (int) $removeDto->userId) {
            $this->logService->createLog(new CreateLogDto('notification_remove', json_encode([
                'notification' => $notification,
                'user_id' => $removeDto->userId,
            ]), json_encode([
                'status' => 'error',
                'message' => 'You are not authorized to delete this notification',
            ]), 'error'));
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
