<?php

namespace Notification\Presentation;

use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Business\Dtos\RemoveDto;
use Notification\Business\Usecases\MarkAsReadService;
use Notification\Business\Usecases\MarkAsUnreadService;
use Notification\Business\Usecases\MigrateService;
use Notification\Business\Usecases\MyNotificationsService;
use Notification\Business\Usecases\RemoveService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

/**
 * Notification Controller
 */
class NotificationController
{
    private MyNotificationsService $myNotificationsService;
    private MarkAsReadService $markAsReadService;
    private MarkAsUnreadService $markAsUnreadService;
    private RemoveService $removeService;
    private MigrateService $migrateService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;
    private JsonResponseServiceInterface $jsonResponseService;

    public function __construct(
        MyNotificationsService $myNotificationsService,
        MarkAsReadService $markAsReadService,
        MarkAsUnreadService $markAsUnreadService,
        RemoveService $removeService,
        MigrateService $migrateService,
        Request $request,
        ApiCredentialServiceInterface $apiCredentialService,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->myNotificationsService = $myNotificationsService;
        $this->markAsReadService = $markAsReadService;
        $this->markAsUnreadService = $markAsUnreadService;
        $this->removeService = $removeService;
        $this->migrateService = $migrateService;
        $this->request = $request;
        $this->apiCredentialService = $apiCredentialService;
        $this->jsonResponseService = $jsonResponseService;
    }

    public function myNotifications()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $notifications = $this->myNotificationsService->query(new MyNotificationsDto(
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
        $notification = $this->markAsReadService->execute(new MarkAsReadDto(
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
        $notification = $this->markAsUnreadService->execute(new MarkAsUnreadDto(
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
        $notification = $this->removeService->execute(new RemoveDto(
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
        $result = $this->migrateService->execute();
        return $this->jsonResponseService->success([
            'result' => $result,
            'message' => 'Notifications migrated successfully'
        ]);
    }
}
