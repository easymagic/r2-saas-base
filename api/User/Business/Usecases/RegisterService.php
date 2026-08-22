<?php
namespace User\Business\Usecases;

use Exception;
use User\Business\Dtos\RegisterDto;
use User\Business\Usecases\Mail\SendAccountVerifyOtpToUserService;
use User\Data\UserEntity;
use User\Data\UserRepositoryInterface;

class RegisterService
{
    private UserRepositoryInterface $userRepository;
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;
    private SendAccountVerifyOtpToUserService $sendAccountVerifyOtpToUserService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService,
        SendAccountVerifyOtpToUserService $sendAccountVerifyOtpToUserService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
        $this->sendAccountVerifyOtpToUserService = $sendAccountVerifyOtpToUserService;
    }

    public function execute(RegisterDto $registerDto)
    {
        $user = $this->userRepository->query([
            "email" => $registerDto->email,
        ])->fetchOne();
        if (!$user->isEmpty()) {
            throw new Exception('User already exists!');
        }

        $user = $this->userRepository->save(new UserEntity([
            'email' => $registerDto->email,
            'password' => password_hash($registerDto->password, PASSWORD_DEFAULT),
            'name' => $registerDto->name,
            'phone' => $registerDto->phone,
            'delivery_address' => $registerDto->delivery_address,
            'social_security_number' => $registerDto->social_security_number,
            'role' => $registerDto->role,
            'status' => $registerDto->status,
            'country_code' => $registerDto->country_code,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
        $this->refreshTokenService->execute($user->id);
        $user = $this->refreshOtpService->execute($user->id);
        $this->sendAccountVerifyOtpToUserService->execute($user->id);
        return $user;
    }
}
