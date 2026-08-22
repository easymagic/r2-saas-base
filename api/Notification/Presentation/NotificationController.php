<?php

namespace Notification\Presentation;

use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Business\Dtos\RemoveDto;
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
        $notifications = $this->notificationService->myNotifications(new MyNotificationsDto(
            (int) $user->id
        ));
        $count = $notifications->count();
        $notifications = $notifications->fetch();
        return $this->jsonResponseService->success([
            'notifications' => $notifications,
            'count' => $count
        ]);
    }

    public function markAsRead()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $notification = $this->notificationService->markAsRead(new MarkAsReadDto(
            (int) $this->request->get('notification_id'),
            (int) $user->id
        ));
        return $this->jsonResponseService->success([
            'notification' => $notification,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAsUnread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $notification = $this->notificationService->markAsUnread(new MarkAsUnreadDto(
            (int) $this->request->get('notification_id'),
            (int) $user->id
        ));
        return $this->jsonResponseService->success([
            'notification' => $notification,
            'message' => 'Notification marked as unread'
        ]);
    }

    public function delete()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $notification = $this->notificationService->remove(new RemoveDto(
            (int) $this->request->get('notification_id'),
            (int) $user->id
        ));
        return $this->jsonResponseService->success([
            'notification' => $notification,
            'message' => 'Notification deleted successfully'
        ]);
    }

    public function migrate()
    {
        $result = $this->notificationService->migrate();
        return $this->jsonResponseService->success([
            'result' => $result,
            'message' => 'Notifications migrated successfully'
        ]);
    }
}
