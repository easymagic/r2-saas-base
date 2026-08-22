<?php

namespace Thread\Business;

use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use Thread\Data\ThreadRepositoryInterface;
use Thread\Data\ThreadEntity;
use Thread\Data\ThreadMigrationRepositoryInterface;
use User\Data\UserRepositoryInterface;

/**
 * @extends AbstractBaseService<ThreadEntity, ThreadRepositoryInterface>
 */
class ThreadService extends AbstractBaseService implements ThreadServiceInterface
{
    private ThreadMigrationRepositoryInterface $threadMigrationRepositoryInterface;
    private ThreadRepositoryInterface $threadRepository;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private FileUploadServiceInterface $fileUploadService;
    private ThreadNotificationServiceInterface $threadNotificationService;
    private NotificationServiceInterface $notificationService;
    private UserRepositoryInterface $userRepository;


    public function __construct(
        ThreadMigrationRepositoryInterface $threadMigrationRepositoryInterface,
        ThreadRepositoryInterface $threadRepository,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        FileUploadServiceInterface $fileUploadService,
        ThreadNotificationServiceInterface $threadNotificationService,
        NotificationServiceInterface $notificationService,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($threadRepository);
        $this->threadMigrationRepositoryInterface = $threadMigrationRepositoryInterface;
        $this->threadRepository = $threadRepository;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->fileUploadService = $fileUploadService;
        $this->threadNotificationService = $threadNotificationService;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    public function migrate()
    {
        return $this->threadMigrationRepositoryInterface->migrate();
    }

    /**
     * @param int $order_id
     * @param int $sender_id
     * @param string $message
     * @param array $attachment_url
     * @return ThreadEntity
     */
    public function createThread(int $order_id, int $sender_id, string $message, array $attachment_url = [])
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }
        if (empty($sender_id)) {
            throw new Exception('Sender ID is required');
        }
        if (empty($message)) {
            throw new Exception('Message is required');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }

        $sender = $this->userRepository->find($sender_id);
        if ($sender->isEmpty()) {
            throw new Exception('Sender not found');
        }

        $path = '/uploads/threads';
        $full_path = __DIR__ . '/../../';
        $attachment_path = '';

        if (!empty($attachment_url)) {
            $uploaded = $this->fileUploadService->uploadFile($attachment_url, $path, $full_path);
            if ($uploaded) {
                $attachment_path = $uploaded;
            }
        }

         $thread = $this->threadRepository->save(0, [
            'order_id' => $order_id,
            'sender_id' => $sender_id,
            'message' => $message,
            'attachment_url' => $attachment_path,
        ]);

        if ($order->user_id != $sender_id) {
            $this->threadNotificationService->sendNotificationToUser($thread->id);
        }

        $this->notificationService->create(new NotificationCreateDto(
            (int) $order->user_id,
            'New Message',
            'You have a new message from ' . $sender->name
        ));

        return $thread;
    }

    /**
     * @param int $order_id
     * @param array $filters
     * @return QueryObject
     */
    public function getThreadListForOrder(int $order_id, array $filters = [])
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }

        $order = $this->snappyOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            throw new Exception('Order not found');
        }

        $filters['order_id'] = $order_id;
        return $this->threadRepository->query($filters);
    }
}
