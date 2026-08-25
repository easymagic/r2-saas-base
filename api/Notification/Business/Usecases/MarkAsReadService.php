<?php
namespace Notification\Business\Usecases;

use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Data\NotificationEntity;
use Notification\Data\NotificationRepositoryInterface;
use Shared\Contracts;

class MarkAsReadService
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

    public function execute(MarkAsReadDto $markAsReadDto)
    {
        $notification = $this->notificationRepository->find($markAsReadDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');

        $authorized = (int) $notification->user_id === (int) $markAsReadDto->userId;
        Contracts::requires($authorized, 'You are not authorized to mark this notification as read');

        return $this->markAsRead($authorized, $notification, $markAsReadDto);
    }

    private function markAsRead(bool $authorized, NotificationEntity $notification, MarkAsReadDto $markAsReadDto){
        if ($authorized){
            $this->logService->createLog(new CreateLogDto('notification_mark_as_read', json_encode([
                'notification' => $notification,
                'user_id' => $markAsReadDto->userId,
            ]), json_encode([
                'notification' => $notification,
                'user_id' => $markAsReadDto->userId,
            ]), 'info'));    
            $notification->read_at = date('Y-m-d H:i:s');
            $notification->is_read = 1;
            $notification->updated_at = date('Y-m-d H:i:s');
            return $this->notificationRepository->save($notification);    
        }
    }
}
