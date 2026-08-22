<?php
namespace User\Business\Usecases;

use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use User\Business\Dtos\LoginDto;
use User\Data\UserRepositoryInterface;

class LoginService
{
    private UserRepositoryInterface $userRepository;
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;
    private NotificationServiceInterface $notificationService;
    private PlatformConfigServiceInterface $platformConfigService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService,
        NotificationServiceInterface $notificationService,
        PlatformConfigServiceInterface $platformConfigService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
        $this->notificationService = $notificationService;
        $this->platformConfigService = $platformConfigService;
    }

    public function execute(LoginDto $loginDto)
    {
        $user = $this->userRepository->query([
            'email' => $loginDto->email,
        ])->fetchOne();
        if (password_verify($loginDto->password, $user->password)) {
            $this->refreshTokenService->execute($user->id);
            $user = $this->refreshOtpService->execute($user->id);
            $this->notificationService->create(new NotificationCreateDto(
                (int) $user->id,
                'Login successful',
                'You have successfully logged in to your account.'
            ));
            $this->platformConfigService->set(new SetDto('app_version', '1.0.0'));
            return $user;
        }
        throw new Exception('Invalid credentials!');
    }
}
