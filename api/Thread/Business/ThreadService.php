<?php

namespace Thread\Business;

use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\AbstractBaseService;
use Shared\Contracts;
use Shared\Query\QueryObject;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use Thread\Business\Dtos\CreateThreadDto;
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

    public function createThread(CreateThreadDto $createThreadDto)
    {
        $order = $this->snappyOrderRepository->find($createThreadDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $sender = $this->userRepository->find($createThreadDto->sender_id);
        Contracts::requireEntityFound($sender, 'Sender');

        $path = '/uploads/threads';
        $full_path = __DIR__ . '/../../';
        $attachment_path = '';

        if (!empty($createThreadDto->attachment_url)) {
            $uploaded = $this->fileUploadService->uploadFile($createThreadDto->attachment_url, $path, $full_path);
            if ($uploaded) {
                $attachment_path = $uploaded;
            }
        }

        $thread = $this->threadRepository->save(new ThreadEntity([
            'order_id' => $createThreadDto->order_id,
            'sender_id' => $createThreadDto->sender_id,
            'message' => $createThreadDto->message,
            'attachment_url' => $attachment_path,
        ]));

        if ($order->user_id != $createThreadDto->sender_id) {
            $this->threadNotificationService->sendNotificationToUser($thread->id);
        }

        $this->notificationService->create(new NotificationCreateDto(
            (int) $order->user_id,
            'New Message',
            'You have a new message from ' . $sender->name
        ));

        return $thread;
    }

    public function getThreadListForOrder(int $order_id, array $filters = [])
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->snappyOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $filters['order_id'] = $order_id;
        return $this->threadRepository->query($filters);
    }
}
