<?php
namespace User\Business\Usecases;

use User\Business\Dtos\CreateDto;
use User\Data\UserEntity;
use User\Data\UserRepositoryInterface;

class CreateService
{
    private UserRepositoryInterface $userRepository;
    private RefreshTokenService $refreshTokenService;
    private RefreshOtpService $refreshOtpService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenService $refreshTokenService,
        RefreshOtpService $refreshOtpService
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenService = $refreshTokenService;
        $this->refreshOtpService = $refreshOtpService;
    }

    public function execute(CreateDto $createDto)
    {
        $user = $this->userRepository->save(new UserEntity([
            'email' => $createDto->email,
            'password' => password_hash($createDto->password, PASSWORD_DEFAULT),
            'name' => $createDto->name,
            'phone' => $createDto->phone,
            'delivery_address' => $createDto->delivery_address,
            'social_security_number' => $createDto->social_security_number,
            'role' => $createDto->role,
            'status' => $createDto->status,
            'country_code' => $createDto->country_code,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
        $this->refreshTokenService->execute($user->id);
        $user = $this->refreshOtpService->execute($user->id);
        return $user;
    }
}
