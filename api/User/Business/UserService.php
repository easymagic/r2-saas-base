<?php

namespace User\Business;

use Shared\AbstractBaseService;
use Shared\Contracts;
use User\Business\AccountMailNotificationServiceInterface;
use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\PlatformConfigServiceInterface;
use User\Data\UserRepositoryInterface;
use Exception;
use Notification\Business\Dtos\CreateDto as NotificationCreateDto;
use Notification\Business\NotificationServiceInterface;
use User\Business\Dtos\ChangePasswordDto;
use User\Business\Dtos\CreateDto;
use User\Business\Dtos\LoginDto;
use User\Business\Dtos\RegisterDto;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Dtos\ResetPasswordDto;
use User\Business\Dtos\TopUpWalletDto;
use User\Business\Dtos\UpdatePasswordDto;
use User\Business\Dtos\UpdateProfileDto;
use User\Business\Dtos\UpdateUserDto;
use User\Business\Dtos\VerifyEmailDto;
use User\Business\Dtos\WithdrawWalletDto;
use User\Data\UserEntity;
use User\Data\UserMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<UserEntity, UserRepositoryInterface>
 */
class UserService extends AbstractBaseService implements UserServiceInterface
{

    private UserMigrationRepositoryInterface $userMigrationRepositoryInterface;
    private UserRepositoryInterface $userRepository;
    private AccountMailNotificationServiceInterface $accountMailNotificationServiceInterface;
    private NotificationServiceInterface $notificationServiceInterface;
    private PlatformConfigServiceInterface $platformConfigServiceInterface;


    public function __construct(
        UserMigrationRepositoryInterface $userMigrationRepositoryInterface,
        UserRepositoryInterface $userRepository,
        AccountMailNotificationServiceInterface $accountMailNotificationServiceInterface,
        NotificationServiceInterface $notificationServiceInterface,
        PlatformConfigServiceInterface $platformConfigServiceInterface
    ) {
        parent::__construct($userRepository);
        $this->userMigrationRepositoryInterface = $userMigrationRepositoryInterface;
        $this->userRepository = $userRepository;
        $this->accountMailNotificationServiceInterface = $accountMailNotificationServiceInterface;
        $this->notificationServiceInterface = $notificationServiceInterface;
        $this->platformConfigServiceInterface = $platformConfigServiceInterface;
    }

    public function login(LoginDto $loginDto)
    {
        $user = $this->userRepository->query([
            'email' => $loginDto->email,
        ])->fetchOne();
        if (password_verify($loginDto->password, $user->password)) {
            $this->refreshToken($user->id);
            $user = $this->refreshOtp($user->id);
            $this->notificationServiceInterface->create(new NotificationCreateDto(
                (int) $user->id,
                'Login successful',
                'You have successfully logged in to your account.'
            ));
            $this->platformConfigServiceInterface->set(new SetDto('app_version', '1.0.0'));
            return $user;
        }
        throw new Exception('Invalid credentials!');
    }

    public function register(RegisterDto $registerDto)
    {
        $user = $this->userRepository->query([
            "email" => $registerDto->email,
        ])->fetchOne();
        if (!$user->isEmpty()){
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
        $this->refreshToken($user->id);
        $user = $this->refreshOtp($user->id);
        $this->accountMailNotificationServiceInterface->sendAccountVerifyOtpToUser($user->id);
        return $user;
    }

    public function create(CreateDto $createDto)
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
        $this->refreshToken($user->id);
        $user = $this->refreshOtp($user->id);
        return $user;
    }

    public function updateUser(UpdateUserDto $updateUserDto)
    {
        $user = $this->userRepository->find($updateUserDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->name = $updateUserDto->name;
        $user->phone = $updateUserDto->phone;
        $user->delivery_address = $updateUserDto->delivery_address;
        $user->social_security_number = $updateUserDto->social_security_number;
        $user->role = $updateUserDto->role;
        $user->status = $updateUserDto->status;
        $user->country_code = $updateUserDto->country_code;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function delete(int $id)
    {
        Contracts::requires($id > 0, 'ID is required');
        throw new Exception('Delete not allowed');
    }

    public function updateProfile(UpdateProfileDto $updateProfileDto)
    {
        $user = $this->userRepository->find($updateProfileDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->name = $updateProfileDto->name;
        $user->phone = $updateProfileDto->phone;
        $user->delivery_address = $updateProfileDto->delivery_address;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function updatePassword(UpdatePasswordDto $updatePasswordDto)
    {
        $user = $this->userRepository->find($updatePasswordDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->password = password_hash($updatePasswordDto->password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function changePassword(ChangePasswordDto $changePasswordDto)
    {
        $user = $this->userRepository->find($changePasswordDto->id);
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires(
            password_verify($changePasswordDto->old_password, $user->password),
            'Old password is incorrect'
        );
        $user->password = password_hash($changePasswordDto->new_password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function getWalletBalance(int $id)
    {
        $user = $this->userRepository->find($id);
        return $user->wallet_balance;
    }

    public function logout(int $userId)
    {
        $this->refreshToken($userId);
        $this->refreshOtp($userId);
        return true;
    }

    public function requestForgotPassword(RequestForgotPasswordDto $requestForgotPasswordDto)
    {
        $user = $this->userRepository->query([
            'email' => $requestForgotPasswordDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        $this->refreshOtp($user->id);
        $user = $this->refreshToken($user->id);
        $this->accountMailNotificationServiceInterface->sendAccountForgotPasswordOtpToUser($user->id);
        return true;
    }

    public function resetPassword(ResetPasswordDto $resetPasswordDto)
    {
        $user = $this->userRepository->query([
            'email' => $resetPasswordDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires($user->otp == $resetPasswordDto->otp, 'OTP is incorrect');
        $user->password = password_hash($resetPasswordDto->password, PASSWORD_DEFAULT);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function verifyEmail(VerifyEmailDto $verifyEmailDto)
    {
        $user = $this->userRepository->query([
            'email' => $verifyEmailDto->email,
        ])->fetchOne();
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires($user->otp == $verifyEmailDto->otp, 'OTP is incorrect');
        $user->status = 'active';
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function migrate()
    {
        return $this->userMigrationRepositoryInterface->migrate();
    }

    public function topUpWallet(TopUpWalletDto $topUpWalletDto)
    {
        $user = $this->userRepository->find($topUpWalletDto->id);
        Contracts::requireEntityFound($user, 'User');
        $user->wallet_balance = $user->wallet_balance + $topUpWalletDto->amount;
        $user->updated_at = date('Y-m-d H:i:s');
        $user = $this->userRepository->save($user);
        $this->accountMailNotificationServiceInterface->sendAccountTopUpWalletToUser(
            $user->id,
            $topUpWalletDto->amount
        );
        return $user;
    }

    public function withdrawWallet(WithdrawWalletDto $withdrawWalletDto)
    {
        $user = $this->userRepository->find($withdrawWalletDto->id);
        Contracts::requireEntityFound($user, 'User');
        Contracts::requires(
            $user->wallet_balance >= $withdrawWalletDto->amount,
            'Balance is not enough'
        );
        $user->wallet_balance = $user->wallet_balance - $withdrawWalletDto->amount;
        $user->updated_at = date('Y-m-d H:i:s');
        $user = $this->userRepository->save($user);
        $this->accountMailNotificationServiceInterface->sendAccountWithdrawWalletToUser(
            $user->id,
            $withdrawWalletDto->amount
        );
        return $user;
    }

    public function refreshToken(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $token = bin2hex(random_bytes(32));
        $user->token = $userId . "_" . $token;
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function refreshOtp(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $user->otp = (string) rand(100000, 999999);
        $user->updated_at = date('Y-m-d H:i:s');
        return $this->userRepository->save($user);
    }

    public function fetchUsersAsAdmin(array $filters = [])
    {
        return $this->userRepository->query($filters);
    }
}
