<?php

namespace Presentation\Http\Controllers\User;

use Application\User\UserServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use R2Packages\Framework\Request;


class UserController
{
    private UserServiceInterface $userService;

    private JsonResponseServiceInterface $jsonResponseService;
    private Request $request;

    public function __construct(
        UserServiceInterface $userService,
        Request $request,
        JsonResponseServiceInterface $jsonResponseService
    ) {
        $this->userService = $userService;
        $this->request = $request;
        $this->jsonResponseService = $jsonResponseService;
    }

    public function login()
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $user = $this->userService->login($email, $password);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Login successful",
        ]);
    }

    function register()
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $name = $this->request->get('name');
        $phone = $this->request->get('phone');
        $delivery_address = $this->request->get('delivery_address');
        $social_security_number = $this->request->get('social_security_number');
        $role = $this->request->get('role');
        $status = $this->request->get('status');
        $country_code = $this->request->get('country_code');
        $user = $this->userService->register(
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
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User registered successfully , please check your email for verification"
        ]);
    }

    function create()
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $name = $this->request->get('name');
        $phone = $this->request->get('phone');
        $delivery_address = $this->request->get('delivery_address');
        $social_security_number = $this->request->get('social_security_number');
        $role = $this->request->get('role');
        $status = $this->request->get('status');
        $country_code = $this->request->get('country_code');

        $user = $this->userService->create(
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
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User created successfully"
        ]);
    }

    function updateUser()
    {
        $id = $this->request->get('user_id');
        $name = $this->request->get('name');
        $phone = $this->request->get('phone');
        $delivery_address = $this->request->get('delivery_address');
        $social_security_number = $this->request->get('social_security_number');
        $role = $this->request->get('role');
        $status = $this->request->get('status');
        $country_code = $this->request->get('country_code');

        $user = $this->userService->updateUser(
            $id,
            $name,
            $phone,
            $delivery_address,
            $social_security_number,
            $role,
            $status,
            $country_code
        );
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User updated successfully"
        ]);
    }

    function delete()
    {
        $id = $this->request->get('user_id');
        $user = $this->userService->delete($id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User deleted successfully"
        ]);
    }

    function updateProfile()
    {
        $id = $this->request->get('user_id');
        $name = $this->request->get('name');
        $phone = $this->request->get('phone');
        $delivery_address = $this->request->get('delivery_address');

        $user = $this->userService->updateProfile(
            $id,
            $name,
            $phone,
            $delivery_address
        );

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Profile updated successfully"
        ]);
    }

    function changePassword()
    {
        $id = $this->request->get('user_id');
        $old_password = $this->request->get('old_password');
        $new_password = $this->request->get('new_password');
        $confirm_password = $this->request->get('confirm_password');

        $user = $this->userService->changePassword(
            $id,
            $old_password,
            $new_password,
            $confirm_password
        );

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Password changed successfully"
        ]);
    }

    function find()
    {
        $id = $this->request->get('user_id');
        $user = $this->userService->find($id);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "User found successfully"
        ]);
    }

    function getWalletBalance()
    {
        $id = $this->request->get('user_id');
        $balance = $this->userService->getWalletBalance($id);
        $this->jsonResponseService->success([
            'balance' => $balance,
            "message" => "Wallet balance fetched successfully"
        ]);
    }

    function logout()
    {
        $id = $this->request->get('user_id');
        $this->userService->logout($id);
        $this->jsonResponseService->success([
            'message' => 'Logged out successfully',
            "status" => "success",
        ]);
    }

    function requestForgotPassword()
    {
        $email = $this->request->get('email');
        $user = $this->userService->requestForgotPassword($email);
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Forgot password request sent successfully to your email",
        ]);
    }

    function resetPassword()
    {
        $email = $this->request->get('email');
        $otp = $this->request->get('otp');
        $password = $this->request->get('password');
        $confirm_password = $this->request->get('confirm_password');

        $user = $this->userService->resetPassword(
            $email,
            $otp,
            $password,
            $confirm_password
        );
        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Password reset successfully",
        ]);
    }

    function verifyEmail()
    {
        $email = $this->request->get('email');
        $otp = $this->request->get('otp');

        $user = $this->userService->verifyEmail($email, $otp);

        $this->jsonResponseService->success([
            'user' => $user,
            "message" => "Email verified successfully",
        ]);
    }

    function fetch()
    {
        $data = $this->request->all();
        $users = $this->userService->fetch($data);
        $count = $this->userService->count($data);
        $this->jsonResponseService->success([
            'users' => $users,
            'count' => $count,
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
}
