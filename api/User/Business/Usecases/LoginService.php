<?php
namespace User\Business\Usecases;

use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\Usecases\CreateService as NotificationCreateService;
use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\Usecases\SetService;
use User\Business\Dtos\LoginDto;
use User\Data\UserRepositoryInterface;

class LoginService
{
    private UserRepositoryInterface $userRepository;
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;
    private NotificationCreateService $notificationCreateService;
    private SetService $setService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService,
        NotificationCreateService $notificationCreateService,
        SetService $setService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
        $this->notificationCreateService = $notificationCreateService;
        $this->setService = $setService;
    }

    public function execute(LoginDto $loginDto)
    {
        $user = $this->userRepository->query([
            'email' => $loginDto->email,
        ])->fetchOne();
        if (password_verify($loginDto->password, $user->password)) {
            $this->refreshTokenService->execute($user->id);
            $user = $this->refreshOtpService->execute($user->id);
            $this->notificationCreateService->execute(new NotificationCreateDto(
                (int) $user->id,
                'Login successful',
                'You have successfully logged in to your account.'
            ));
            $this->setService->execute(new SetDto('app_version', '1.0.0'));
            return $user;
        }
        throw new Exception('Invalid credentials!');
    }
}
