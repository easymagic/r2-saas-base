<?php
namespace User\Business\Usecases;

class LogoutService
{
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;

    public function __construct(
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService
    ) {
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
    }

    public function execute(int $userId)
    {
        $this->refreshTokenService->execute($userId);
        $this->refreshOtpService->execute($userId);
        return true;
    }
}
