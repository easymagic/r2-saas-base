<?php
namespace Notification\Business\Usecases;

use Notification\Business\Dtos\CreateDto;
use Notification\Data\NotificationEntity;
use Notification\Data\NotificationRepositoryInterface;

class CreateService
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(CreateDto $createDto)
    {
        return $this->notificationRepository->save(new NotificationEntity([
            'user_id' => $createDto->userId,
            'title' => $createDto->title,
            'message' => $createDto->message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }
}
