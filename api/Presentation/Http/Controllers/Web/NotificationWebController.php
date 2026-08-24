<?php

namespace Presentation\Http\Controllers\Web;

use Notification\Business\Dtos\MarkAsReadDto;
use Notification\Business\Dtos\MarkAsUnreadDto;
use Notification\Business\Dtos\MyNotificationsDto;
use Notification\Business\Dtos\RemoveDto;
use Notification\Business\Usecases\MarkAsReadService;
use Notification\Business\Usecases\MarkAsUnreadService;
use Notification\Business\Usecases\MyNotificationsService;
use Notification\Business\Usecases\RemoveService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;

class NotificationWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private MyNotificationsService $myNotificationsService;
    private MarkAsReadService $markAsReadService;
    private MarkAsUnreadService $markAsUnreadService;
    private RemoveService $removeService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        MyNotificationsService $myNotificationsService,
        MarkAsReadService $markAsReadService,
        MarkAsUnreadService $markAsUnreadService,
        RemoveService $removeService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->myNotificationsService = $myNotificationsService;
        $this->markAsReadService = $markAsReadService;
        $this->markAsUnreadService = $markAsUnreadService;
        $this->removeService = $removeService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $query = $this->myNotificationsService->query(new MyNotificationsDto((int) $user->id));
        View::render('notifications/index', [
            'title' => 'Notifications',
            'subtitle' => 'Your alerts',
            'nav' => 'notifications',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'notifications' => $query->fetchAll(),
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function markRead()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->markAsReadService->execute(new MarkAsReadDto(
                (int) $this->request->get('notification_id'),
                (int) $user->id
            ));
            WebSession::flash('success', 'Marked as read.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/notifications');
    }

    public function markUnread()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->markAsUnreadService->execute(new MarkAsUnreadDto(
                (int) $this->request->get('notification_id'),
                (int) $user->id
            ));
            WebSession::flash('success', 'Marked as unread.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/notifications');
    }

    public function delete()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->removeService->execute(new RemoveDto(
                (int) $this->request->get('notification_id'),
                (int) $user->id
            ));
            WebSession::flash('success', 'Notification deleted.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/notifications');
    }

}
