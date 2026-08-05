<?php

namespace Business\User;

use Business\AbstractBaseService;
use Business\User\AccountMailNotificationServiceInterface;
use Business\Notifications\NotificationServiceInterface;

use Data\User\UserMigrationRepositoryInterface;
use Business\PlatformConfig\PlatformConfigServiceInterface;
use Data\User\UserRepositoryInterface;
use Exception;
use Data\User\UserEntity;

class UserService extends AbstractBaseService implements UserServiceInterface
{

    private UserMigrationRepositoryInterface $userMigrationRepositoryInterface;
    private UserValidationServiceInterface $userValidationService;
    private UserRepositoryInterface $userRepository;
    private AccountMailNotificationServiceInterface $accountMailNotificationServiceInterface;
    private NotificationServiceInterface $notificationServiceInterface;
    private PlatformConfigServiceInterface $platformConfigServiceInterface;


    public function __construct(
        UserMigrationRepositoryInterface $userMigrationRepositoryInterface,
        UserValidationServiceInterface $userValidationService,
        UserRepositoryInterface $userRepository,
        AccountMailNotificationServiceInterface $accountMailNotificationServiceInterface,
        NotificationServiceInterface $notificationServiceInterface,
        PlatformConfigServiceInterface $platformConfigServiceInterface
    ) {
        parent::__construct($userRepository);
        $this->userMigrationRepositoryInterface = $userMigrationRepositoryInterface;
        $this->userValidationService = $userValidationService;
        $this->userRepository = $userRepository;
        $this->accountMailNotificationServiceInterface = $accountMailNotificationServiceInterface;
        $this->notificationServiceInterface = $notificationServiceInterface;
        $this->platformConfigServiceInterface = $platformConfigServiceInterface;
    }

    public function login(string $email, string $password)
    {
        $this->userValidationService->validateLogin($email, $password);
        $user = $this->userRepository->findBy('email', $email);
        if (password_verify($password, $user->password)) {
            $this->refreshToken($user->id);
            $user = $this->refreshOtp($user->id);
            $this->notificationServiceInterface->create(
                $user->id,
                'Login successful',
                'You have successfully logged in to your account.'
            );
            $this->platformConfigServiceInterface->set('app_version', '1.0.0');
            return $user;
        }
        throw new Exception('Invalid credentials!');
    }

    public function register(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    ) {
        $this->userValidationService->validateRegister(
            $email,
            $password,
            $name,
            $phone,
            $delivery_address,
            $social_security_number,
            $role,
            $status,
            $country_code
        );
        $user = $this->userRepository->save(0, [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'phone' => $phone,
            'delivery_address' => $delivery_address,
            'social_security_number' => $social_security_number,
            'role' => $role,
            'status' => $status,
            'country_code' => $country_code,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->refreshToken($user->id);
        $user = $this->refreshOtp($user->id);
        $this->accountMailNotificationServiceInterface->sendAccountVerifyOtpToUser($user->id);
        return $user;
    }

    public function create(
        string $email,
        string $password,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    ) {

        $this->userValidationService->validateCreate(
            $email,
            $password,
            $name,
            $phone,
            $delivery_address,
            $social_security_number,
            $role,
            $status,
            $country_code
        );
        $user = $this->userRepository->save(0, [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'phone' => $phone,
            'delivery_address' => $delivery_address,
            'social_security_number' => $social_security_number,
            'role' => $role,
            'status' => $status,
            'country_code' => $country_code,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->refreshToken($user->id);
        $user = $this->refreshOtp($user->id);
        return $user;
    }

    public function updateUser(
        int $id,
        string $name,
        string $phone,
        string $delivery_address,
        string $social_security_number,
        string $role,
        string $status,
        string $country_code
    ) {
        $this->userValidationService->validateUpdateUser(
            $id,
            $name,
            $phone,
            $delivery_address,
            $social_security_number,
            $role,
            $status,
            $country_code
        );
        $user = $this->userRepository->save($id, [
            'name' => $name,
            'phone' => $phone,
            'delivery_address' => $delivery_address,
            'social_security_number' => $social_security_number,
            'role' => $role,
            'status' => $status,
            'country_code' => $country_code,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function delete(int $id)
    {
        $user = $this->userValidationService->validateDelete($id);
        $this->userRepository->delete($id);
        return $user;
    }

    public function updateProfile(
        int $id,
        string $name,
        string $phone,
        string $delivery_address
    ) {
        $this->userValidationService->validateUpdateProfile(
            $id,
            $name,
            $phone,
            $delivery_address
        );
        $user = $this->userRepository->save($id, [
            'name' => $name,
            'phone' => $phone,
            'delivery_address' => $delivery_address,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function updatePassword(int $id, string $password)
    {
        $this->userValidationService->validateUpdatePassword($id, $password);
        $user = $this->userRepository->save($id, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function changePassword(
        int $id,
        string $old_password,
        string $new_password,
        string $confirm_password
    ) {
        $this->userValidationService->validateChangePassword(
            $id,
            $old_password,
            $new_password,
            $confirm_password
        );
        $user = $this->userRepository->save($id, [
            'password' => password_hash($new_password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function find(int $id)
    {
        $user = $this->userRepository->find($id);
        return $user;
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

    /**
     * @param string $email
     * @return mixed
     */
    public function requestForgotPassword(string $email)
    {
        $user = $this->userRepository->findBy('email', $email);
        $this->refreshOtp($user->id);
        $user = $this->refreshToken($user->id);
        $this->accountMailNotificationServiceInterface->sendAccountForgotPasswordOtpToUser($user->id);
        return true;
    }

    /**
     * @param string $email
     * @param string $otp
     * @param string $password
     * @param string $confirm_password
     * @return mixed
     */
    public function resetPassword(
        string $email,
        string $otp,
        string $password,
        string $confirm_password
    ) {
        $user = $this->userRepository->findBy('email', $email);
        $this->userValidationService->validateResetPassword(
            $email,
            $otp,
            $password,
            $confirm_password
        );
        $user = $this->userRepository->save($user->id, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    /**
     * @param string $email
     * @email string
     * @otp string
     * @return UserEntity
     */
    public function verifyEmail(string $email, string $otp)
    {
        $this->userValidationService->validateVerifyEmail($email, $otp);
        $user = $this->userRepository->findBy('email', $email);
        $user = $this->userRepository->save($user->id, [
            "status" => "active",
            'email_verified_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function migrate()
    {
        return $this->userMigrationRepositoryInterface->migrate();
    }

    public function topUpWallet(int $id, float $amount)
    {
        $this->userValidationService->validateTopUpWallet($id, $amount);
        $user = $this->userRepository->find($id);
        $user = $this->userRepository->save($user->id, [
            'wallet_balance' => $user->wallet_balance + $amount,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->accountMailNotificationServiceInterface->sendAccountTopUpWalletToUser($user->id, $amount);
        return $user;
    }

    public function withdrawWallet(int $id, float $amount)
    {
        $this->userValidationService->validateWithdrawWallet($id, $amount);
        $user = $this->userRepository->find($id);
        $user = $this->userRepository->save($user->id, [
            'wallet_balance' => $user->wallet_balance - $amount,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        // $this->walletService->log(
        //     $user->id,
        //     $amount,
        //     uniqid("WALLET_WITHDRAWAL_"),
        //     'withdrawal',
        //     'Withdrawal from wallet',
        //     'approved'
        // );
        $this->accountMailNotificationServiceInterface->sendAccountWithdrawWalletToUser($user->id, $amount);
        return $user;
    }

    public function refreshToken(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $token =  bin2hex(random_bytes(32));
        $user = $this->userRepository->save($user->id, [
            'token' => $userId . "_" . $token,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }

    public function refreshOtp(int $userId)
    {
        $user = $this->userRepository->find($userId);
        $otp = rand(100000, 999999);
        $user = $this->userRepository->save($user->id, [
            'otp' => $otp,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $user;
    }
}
