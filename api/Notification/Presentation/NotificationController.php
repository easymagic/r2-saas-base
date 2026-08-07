<?php

namespace Notification\Presentation;

use Notification\Business\NotificationServiceInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
/**
 * Notification Controller
 */
class NotificationController
{
    private NotificationServiceInterface $notificationService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;
    private JsonResponseServiceInterface $jsonResponseService;

    public function __construct(
        NotificationServiceInterface $notificationService,
        Request $request,
        ApiCredentialServiceInterface $apiCredentialService,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->notificationService = $notificationService;
        $this->request = $request;
        $this->apiCredentialService = $apiCredentialService;
        $this->jsonResponseService = $jsonResponseService;
    }

    public function myNotifications()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = $user->id;
        $notifications = $this->notificationService->myNotifications($userId);
        $count = $this->notificationService->count($userId);
        return $this->jsonResponseService->success([
            "notifications" => $notifications,
            "count" => $count
        ]);
    }

    public function markAsRead()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = $user->id;
        $notificationId = $this->request->get('notification_id');
        $notification = $this->notificationService->markAsRead($notificationId, $userId);
        return $this->jsonResponseService->success([
            "notification" => $notification,
            "message" => "Notification marked as read"
        ]);
    }

    public function markAsUnread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = $user->id;
        $notificationId = $this->request->get('notification_id');
        $notification = $this->notificationService->markAsUnread($notificationId, $userId);
        return $this->jsonResponseService->success([
            "notification" => $notification,
            "message" => "Notification marked as unread"
        ]);
    }

    public function delete()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $userId = $user->id;
        $notificationId = $this->request->get('notification_id');
        $notification = $this->notificationService->delete($notificationId, $userId);
        return $this->jsonResponseService->success([
            "notification" => $notification,
            "message" => "Notification deleted successfully"
        ]);
    }

    public function migrate()
    {
        $result = $this->notificationService->migrate();
        return $this->jsonResponseService->success([
            "result" => $result,
            "message" => "Notifications migrated successfully"
        ]);
    }

}
