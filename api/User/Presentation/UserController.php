<?php

namespace User\Presentation;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use User\Business\Dtos\ChangePasswordDto;
use User\Business\Dtos\CreateDto;
use User\Business\Dtos\LoginDto;
use User\Business\Dtos\RegisterDto;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Dtos\ResetPasswordDto;
use User\Business\Dtos\UpdateProfileDto;
use User\Business\Dtos\UpdateUserDto;
use User\Business\Dtos\VerifyEmailDto;
use User\Business\UserServiceInterface;
use User\Data\UserRepositoryInterface;

class UserController
{
    private UserServiceInterface $userService;

    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserServiceInterface $userService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService,
        ApiCredentialServiceInterface $apiCredentialService,
        UserRepositoryInterface $userRepository
    ) {
        $this->userService = $userService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
        $this->apiCredentialService = $apiCredentialService;
        $this->userRepository = $userRepository;
    }

    public function login()
    {
        $user = $this->userService->login(new LoginDto(
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

        $user = $this->userService->register($registerDto);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User registered successfully , please check your email for verification"
        ]);
    }

    function create()
    {
        $user = $this->userService->create(new CreateDto(
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
        $user = $this->userService->updateUser(new UpdateUserDto(
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
        $user = $this->userService->delete($id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User deleted successfully"
        ]);
    }

    function updateProfile()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $user = $this->userService->updateProfile(new UpdateProfileDto(
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
        $user = $this->userService->changePassword(new ChangePasswordDto(
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
        $user = $this->userService->find($id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User found successfully"
        ]);
    }

    function getWalletBalance()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $balance = $this->userService->getWalletBalance((int) $authUser->id);
        $this->jsonResponseService->success([
            'balance' => $balance,
            "message" => "Wallet balance fetched successfully"
        ]);
    }

    function logout()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        $this->userService->logout((int) $authUser->id);
        $this->jsonResponseService->success([
            'message' => 'Logged out successfully',
            "status" => "success",
        ]);
    }

    function requestForgotPassword()
    {
        $user = $this->userService->requestForgotPassword(new RequestForgotPasswordDto(
            (string) $this->request->get('email')
        ));
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Forgot password request sent successfully to your email",
        ]);
    }

    function resetPassword()
    {
        $user = $this->userService->resetPassword(new ResetPasswordDto(
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
        $user = $this->userService->verifyEmail(new VerifyEmailDto(
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
        $users = $this->userService->fetchUsersAsAdmin($data);
        $this->jsonResponseService->success([
            'users' => $users->fetch(),
            'count' => $users->count(),
            "message" => "Users fetched successfully",
        ]);
    }

    function migrate()
    {
        $result = $this->userService->migrate();
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
