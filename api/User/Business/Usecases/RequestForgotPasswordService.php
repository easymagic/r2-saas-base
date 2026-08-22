<?php
namespace User\Business\Usecases;

use Shared\Contracts;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Usecases\Mail\SendAccountForgotPasswordOtpToUserService;
use User\Data\UserRepositoryInterface;

class RequestForgotPasswordService
{
    private UserRepositoryInterface $userRepository;
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;
    private SendAccountForgotPasswordOtpToUserService $sendAccountForgotPasswordOtpToUserService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService,
        SendAccountForgotPasswordOtpToUserService $sendAccountForgotPasswordOtpToUserService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
        $this->sendAccountForgotPasswordOtpToUserService = $sendAccountForgotPasswordOtpToUserService;
    }

    public function execute(RequestForgotPasswordDto $requestForgotPasswordDto)
    {
        $user = $this->userRepository->query([
            'email' => $requestForgotPasswordDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        $this->refreshOtpService->execute($user->id);
        $user = $this->refreshTokenService->execute($user->id);
        $this->sendAccountForgotPasswordOtpToUserService->execute($user->id);
        return true;
    }
}
