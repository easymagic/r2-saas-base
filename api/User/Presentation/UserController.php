<?php

namespace User\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use Shared\Contracts;
use User\Business\Dtos\ChangePasswordDto;
use User\Business\Dtos\CreateDto;
use User\Business\Dtos\LoginDto;
use User\Business\Dtos\RegisterDto;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Dtos\ResetPasswordDto;
use User\Business\Dtos\UpdateProfileDto;
use User\Business\Dtos\UpdateUserDto;
use User\Business\Dtos\VerifyEmailDto;
use User\Business\Usecases\ChangePasswordService;
use User\Business\Usecases\CreateService;
use User\Business\Usecases\DeleteService;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;
use User\Business\Usecases\LoginService;
use User\Business\Usecases\LogoutService;
use User\Business\Usecases\MigrateService;
use User\Business\Usecases\RegisterService;
use User\Business\Usecases\RequestForgotPasswordService;
use User\Business\Usecases\ResetPasswordService;
use User\Business\Usecases\UpdateProfileService;
use User\Business\Usecases\UpdateUserService;
use User\Business\Usecases\VerifyEmailService;
use User\Data\UserRepositoryInterface;

class UserController
{
    private LoginService $loginService;
    private RegisterService $registerService;
    private CreateService $createService;
    private UpdateUserService $updateUserService;
    private DeleteService $deleteService;
    private UpdateProfileService $updateProfileService;
    private ChangePasswordService $changePasswordService;
    private GetWalletBalanceService $getWalletBalanceService;
    private LogoutService $logoutService;
    private RequestForgotPasswordService $requestForgotPasswordService;
    private ResetPasswordService $resetPasswordService;
    private VerifyEmailService $verifyEmailService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private MigrateService $migrateService;
    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        LoginService $loginService,
        RegisterService $registerService,
        CreateService $createService,
        UpdateUserService $updateUserService,
        DeleteService $deleteService,
        UpdateProfileService $updateProfileService,
        ChangePasswordService $changePasswordService,
        GetWalletBalanceService $getWalletBalanceService,
        LogoutService $logoutService,
        RequestForgotPasswordService $requestForgotPasswordService,
        ResetPasswordService $resetPasswordService,
        VerifyEmailService $verifyEmailService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        MigrateService $migrateService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService,
        UserRepositoryInterface $userRepository
    ) {
        $this->loginService = $loginService;
        $this->registerService = $registerService;
        $this->createService = $createService;
        $this->updateUserService = $updateUserService;
        $this->deleteService = $deleteService;
        $this->updateProfileService = $updateProfileService;
        $this->changePasswordService = $changePasswordService;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->logoutService = $logoutService;
        $this->requestForgotPasswordService = $requestForgotPasswordService;
        $this->resetPasswordService = $resetPasswordService;
        $this->verifyEmailService = $verifyEmailService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->migrateService = $migrateService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
        $this->userRepository = $userRepository;
    }

    public function login()
    {
        $user = $this->loginService->execute(new LoginDto(
            (string) $this->request->get('email'),
            (string) $this->request->get('password')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Login successful",
        ]);
    }

    function register()
    {
        $registerDto = new RegisterDto(
            (string) $this->request->get('email'),
            (string) $this->request->get('password'),
            (string) $this->request->get('name'),
            (string) $this->request->get('phone'),
            (string) $this->request->get('delivery_address'),
            (string) $this->request->get('social_security_number'),
            'customer'
        );
        $registerDto->country_code = (string) $this->request->get('country_code');

        $user = $this->registerService->execute($registerDto);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User registered successfully , please check your email for verification"
        ]);
    }

    function create()
    {
        $user = $this->createService->execute(new CreateDto(
            (string) $this->request->get('email'),
            (string) $this->request->get('password'),
            (string) $this->request->get('name'),
            (string) $this->request->get('phone'),
            (string) $this->request->get('delivery_address'),
            (string) $this->request->get('social_security_number'),
            (string) $this->request->get('role'),
            (string) $this->request->get('status'),
            (string) $this->request->get('country_code')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User created successfully"
        ]);
    }

    function updateUser()
    {
        $user = $this->updateUserService->execute(new UpdateUserDto(
            (int) $this->request->get('user_id'),
            (string) $this->request->get('name'),
            (string) $this->request->get('phone'),
            (string) $this->request->get('delivery_address'),
            (string) $this->request->get('social_security_number'),
            (string) $this->request->get('role'),
            (string) $this->request->get('status'),
            (string) $this->request->get('country_code')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User updated successfully"
        ]);
    }

    function delete()
    {
        $id = (int) $this->request->get('user_id');
        $user = $this->deleteService->execute($id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User deleted successfully"
        ]);
    }

    function updateProfile()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $user = $this->updateProfileService->execute(new UpdateProfileDto(
            (int) $authUser->id,
            (string) $this->request->get('name'),
            (string) $this->request->get('phone'),
            (string) $this->request->get('delivery_address')
        ));

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Profile updated successfully"
        ]);
    }

    function changePassword()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $user = $this->changePasswordService->execute(new ChangePasswordDto(
            (int) $authUser->id,
            (string) $this->request->get('old_password'),
            (string) $this->request->get('new_password'),
            (string) $this->request->get('confirm_password')
        ));

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Password changed successfully"
        ]);
    }

    function find()
    {
        $id = (int) $this->request->get('user_id');
        $user = $this->userRepository->find($id);
        Contracts::requireEntityFound($user, 'User');
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User found successfully"
        ]);
    }

    function getWalletBalance()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $balance = $this->getWalletBalanceService->query((int) $authUser->id);
        $this->jsonResponseService->success([
            'balance' => $balance,
            "message" => "Wallet balance fetched successfully"
        ]);
    }

    function logout()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $this->logoutService->execute((int) $authUser->id);
        $this->jsonResponseService->success([
            'message' => 'Logged out successfully',
            "status" => "success",
        ]);
    }

    function requestForgotPassword()
    {
        $user = $this->requestForgotPasswordService->execute(new RequestForgotPasswordDto(
            (string) $this->request->get('email')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Forgot password request sent successfully to your email",
        ]);
    }

    function resetPassword()
    {
        $user = $this->resetPasswordService->execute(new ResetPasswordDto(
            (string) $this->request->get('email'),
            (string) $this->request->get('otp'),
            (string) $this->request->get('password'),
            (string) $this->request->get('confirm_password')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Password reset successfully",
        ]);
    }

    function verifyEmail()
    {
        $user = $this->verifyEmailService->execute(new VerifyEmailDto(
            (string) $this->request->get('email'),
            (string) $this->request->get('otp')
        ));

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Email verified successfully",
        ]);
    }

    function fetch()
    {
        $data = $this->request->all();
        $users = $this->fetchUsersAsAdminService->query($data);
        $this->jsonResponseService->success([
            'users' => $users->fetch(),
            'count' => $users->count(),
            "message" => "Users fetched successfully",
        ]);
    }

    function migrate()
    {
        $result = $this->migrateService->execute();
        $this->jsonResponseService->success([
            'message' => 'Migration completed successfully',
            'result' => $result,
        ]);
    }

    function me()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $user = $this->userRepository->find((int) $authUser->id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User fetched successfully",
        ]);
    }
}
