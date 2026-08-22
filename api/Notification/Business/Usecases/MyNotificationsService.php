<?php
namespace Notification\Business\Usecases;

use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Data\NotificationRepositoryInterface;

class MyNotificationsService
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function query(MyNotificationsDto $myNotificationsDto)
    {
        return $this->notificationRepository->query([
            'user_id' => $myNotificationsDto->userId,
        ]);
    }
}
