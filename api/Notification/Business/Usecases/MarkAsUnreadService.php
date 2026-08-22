<?php
namespace Notification\Business\Usecases;

use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Data\NotificationRepositoryInterface;
use Shared\Contracts;

class MarkAsUnreadService
{
    private NotificationRepositoryInterface $notificationRepository;
    private LogServiceInterface $logService;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        LogServiceInterface $logService
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->logService = $logService;
    }

    public function execute(MarkAsUnreadDto $markAsUnreadDto)
    {
        $notification = $this->notificationRepository->find($markAsUnreadDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');

        $authorized = (int) $notification->user_id === (int) $markAsUnreadDto->userId;
        if (!$authorized) {
            $this->logService->createLog(new CreateLogDto('notification_mark_as_unread', json_encode([
                'notification' => $notification,
                'user_id' => $markAsUnreadDto->userId,
            ]), json_encode([
                'status' => 'error',
                'message' => 'You are not authorized to mark this notification as unread',
            ]), 'error'));
        }
        Contracts::requires($authorized, 'You are not authorized to mark this notification as unread');

        $notification->is_read = 0;
        $notification->updated_at = date('Y-m-d H:i:s');
        return $this->notificationRepository->save($notification);
    }
}
