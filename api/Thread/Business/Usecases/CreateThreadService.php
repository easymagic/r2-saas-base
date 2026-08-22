<?php
namespace Thread\Business\Usecases;

use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\Usecases\CreateService as NotificationCreateService;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use Thread\Business\Dtos\CreateThreadDto;
use Thread\Business\Usecases\Mail\SendNotificationToUserService;
use Thread\Data\ThreadEntity;
use Thread\Data\ThreadRepositoryInterface;
use User\Data\UserRepositoryInterface;

class CreateThreadService
{
    private ThreadRepositoryInterface $threadRepository;
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private FileUploadServiceInterface $fileUploadService;
    private SendNotificationToUserService $sendNotificationToUserService;
    private NotificationCreateService $notificationCreateService;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ThreadRepositoryInterface $threadRepository,
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        FileUploadServiceInterface $fileUploadService,
        SendNotificationToUserService $sendNotificationToUserService,
        NotificationCreateService $notificationCreateService,
        UserRepositoryInterface $userRepository
    ) {
        $this->threadRepository = $threadRepository;
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->fileUploadService = $fileUploadService;
        $this->sendNotificationToUserService = $sendNotificationToUserService;
        $this->notificationCreateService = $notificationCreateService;
        $this->userRepository = $userRepository;
    }

    public function execute(CreateThreadDto $createThreadDto)
    {
        $order = $this->snappyOrderRepository->find($createThreadDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $sender = $this->userRepository->find($createThreadDto->sender_id);
        Contracts::requireEntityFound($sender, 'Sender');

        $path = '/uploads/threads';
        $full_path = __DIR__ . '/../../../';
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
            $this->sendNotificationToUserService->execute($thread->id);
        }

        $this->notificationCreateService->execute(new NotificationCreateDto(
            (int) $order->user_id,
            'New Message',
            'You have a new message from ' . $sender->name
        ));

        return $thread;
    }
}
