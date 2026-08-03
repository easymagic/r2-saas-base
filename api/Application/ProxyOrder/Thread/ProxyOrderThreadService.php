<?php

namespace Application\ProxyOrder\Thread;

use Application\MailNotifications\Thread\ThreadMailNotificationInterface;
use Domain\ProxyOrder\Interfaces\ProxyOrderThreadRepositoryInterface;
use Infrastructure\ProxyOrder\ProxyOrderRepository;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;

class ProxyOrderThreadService implements ProxyOrderThreadServiceInterface
{
    private ProxyOrderThreadRepositoryInterface $proxyOrderThreadRepository;
    private ThreadMailNotificationInterface $threadMailNotification;
    private ProxyOrderRepository $proxyOrderRepository;
    private FileUploadServiceInterface $fileUploadService;

    public function __construct(
        ProxyOrderThreadRepositoryInterface $proxyOrderThreadRepository,
        ThreadMailNotificationInterface $threadMailNotification,
        ProxyOrderRepository $proxyOrderRepository,
        FileUploadServiceInterface $fileUploadService
    ) {
        $this->proxyOrderThreadRepository = $proxyOrderThreadRepository;
        $this->threadMailNotification = $threadMailNotification;
        $this->proxyOrderRepository = $proxyOrderRepository;
        $this->fileUploadService = $fileUploadService;
    }

    function create(int $proxy_order_id, int $sender_id, string $message, array $attachment_url)
    {
        $proxyOrder = $this->proxyOrderRepository->find($proxy_order_id);

        $path = '/uploads/proxy_order_threads';
        $fullPath = __DIR__ . '/../../';

        $attachment_url = $this->fileUploadService->uploadFile($attachment_url, $path, $fullPath);
        if (!$attachment_url) {
            $attachment_url = '';
        }

        $thread = $this->proxyOrderThreadRepository->save(0, [
            'proxy_order_id' => $proxy_order_id,
            'sender_id' => $sender_id,
            'message' => $message,
            'attachment_url' => $attachment_url,
        ]);

        if ($proxyOrder->user_id != $sender_id){
            $this->threadMailNotification->sendToCustomer($proxyOrder->user_id);
        }

        return $thread;
    }

    function fetchByOrderId(int $orderId) {
        return $this->proxyOrderThreadRepository->filterByOrder($orderId)->fetch();
    }

    function delete(int $id) {
        $thread = $this->proxyOrderThreadRepository->find($id);
        $this->proxyOrderThreadRepository->delete($id);
        return $thread;
    }
}
