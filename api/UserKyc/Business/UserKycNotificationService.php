<?php

namespace UserKyc\Business;

use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use Shared\AbstractBaseService;
use User\Data\UserRepositoryInterface;
use UserKyc\Data\UserKycEntity;
use UserKyc\Data\UserKycRepositoryInterface;

/**
 * @extends AbstractBaseService<UserKycEntity, UserKycRepositoryInterface>
 */
class UserKycNotificationService extends AbstractBaseService implements UserKycNotificationServiceInterface
{
    private UserKycRepositoryInterface $userKycRepository;
    private UserRepositoryInterface $userRepository;
    private NotificationServiceInterface $notificationService;
    private MailServiceInterface $mailService;
    private string $fromEmail = 'noreply@example.com';

    public function __construct(
        UserKycRepositoryInterface $userKycRepository,
        UserRepositoryInterface $userRepository,
        NotificationServiceInterface $notificationService,
        MailServiceInterface $mailService
    ) {
        parent::__construct($userKycRepository);
        $this->userKycRepository = $userKycRepository;
        $this->userRepository = $userRepository;
        $this->notificationService = $notificationService;
        $this->mailService = $mailService;
    }

    public function sendApproveNotification(int $id)
    {
        if (empty($id)) {
            throw new Exception('KYC ID is required');
        }

        $kyc = $this->userKycRepository->find($id);
        if ($kyc->isEmpty()) {
            throw new Exception('KYC record not found');
        }

        $user = $this->userRepository->find($kyc->user_id);
        if ($user->isEmpty()) {
            throw new Exception('User not found');
        }

        $title = 'KYC Approved';
        $message = 'Your store KYC for "' . $kyc->store_name . '" has been approved.';

        $this->notificationService->create(new NotificationCreateDto((int) $user->id, $title, $message));

        $body = 'Hello ' . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . ',<br><br>'
            . 'Your store KYC for <strong>' . htmlspecialchars($kyc->store_name, ENT_QUOTES, 'UTF-8') . '</strong> has been approved.<br><br>'
            . 'Thank you.';

        $this->mailService->send($user->email, $title, $this->fromEmail, $body);
    }

    public function sendRejectNotification(int $id)
    {
        if (empty($id)) {
            throw new Exception('KYC ID is required');
        }

        $kyc = $this->userKycRepository->find($id);
        if ($kyc->isEmpty()) {
            throw new Exception('KYC record not found');
        }

        $user = $this->userRepository->find($kyc->user_id);
        if ($user->isEmpty()) {
            throw new Exception('User not found');
        }

        $reason = trim((string) $kyc->reject_reason);
        $title = 'KYC Rejected';
        $message = 'Your store KYC for "' . $kyc->store_name . '" was rejected.'
            . ($reason !== '' ? ' Reason: ' . $reason : '');

        $this->notificationService->create(new NotificationCreateDto((int) $user->id, $title, $message));

        $body = 'Hello ' . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . ',<br><br>'
            . 'Your store KYC for <strong>' . htmlspecialchars($kyc->store_name, ENT_QUOTES, 'UTF-8') . '</strong> was rejected.<br><br>'
            . ($reason !== '' ? 'Reason: ' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '<br><br>' : '')
            . 'Please update your submission and try again.<br><br>Thank you.';

        $this->mailService->send($user->email, $title, $this->fromEmail, $body);
    }
}
