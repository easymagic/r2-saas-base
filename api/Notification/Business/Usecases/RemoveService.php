<?php
namespace Notification\Business\Usecases;

use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Notification\Business\Dtos\RemoveDto;
use Notification\Data\NotificationRepositoryInterface;
use Shared\Contracts;

class RemoveService
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

    public function execute(RemoveDto $removeDto)
    {
        $notification = $this->notificationRepository->find($removeDto->notificationId);
        Contracts::requireEntityFound($notification, 'Notification');

        $authorized = (int) $notification->user_id === (int) $removeDto->userId;
        if (!$authorized) {
            $this->logService->createLog(new CreateLogDto('notification_remove', json_encode([
                'notification' => $notification,
                'user_id' => $removeDto->userId,
            ]), json_encode([
                'status' => 'error',
                'message' => 'You are not authorized to delete this notification',
            ]), 'error'));
        }
        Contracts::requires($authorized, 'You are not authorized to delete this notification');

        $this->notificationRepository->delete($notification->id);

        return $notification;
    }
}
