<?php

namespace Application\Notifications;

use Domain\Notifications\NotificationRepositoryInterface;
use Exception;

class NotificationService implements NotificationServiceInterface
{

    private NotificationRepositoryInterface $notificationRepository;
    

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
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
      return $this->notificationRepository->filterByUserId($userId)->fetch();
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

    public function delete(int $notificationId, int $userId) {
        $notification = $this->notificationRepository->find($notificationId);
        if ($notification->user_id !== $userId) {
            throw new Exception('You are not authorized to delete this notification');
        }
        $this->notificationRepository->delete($notificationId);
        return true;
    }

    public function count(int $userId) {
        return $this->notificationRepository->filterByUserId($userId)->count();
    }
}
