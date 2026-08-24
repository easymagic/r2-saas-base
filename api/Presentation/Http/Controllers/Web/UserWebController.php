<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use Shared\Contracts;
use User\Business\Dtos\ChangePasswordDto;
use User\Business\Dtos\CreateDto;
use User\Business\Dtos\RequestForgotPasswordDto;
use User\Business\Dtos\ResetPasswordDto;
use User\Business\Dtos\UpdateProfileDto;
use User\Business\Dtos\UpdateUserDto;
use User\Business\Usecases\ChangePasswordService;
use User\Business\Usecases\CreateService;
use User\Business\Usecases\DeleteService;
use User\Business\Usecases\FetchUsersAsAdminService;
use User\Business\Usecases\GetWalletBalanceService;
use User\Business\Usecases\RequestForgotPasswordService;
use User\Business\Usecases\ResetPasswordService;
use User\Business\Usecases\UpdateProfileService;
use User\Business\Usecases\UpdateUserService;
use User\Data\UserRepositoryInterface;

class UserWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private UserRepositoryInterface $userRepository;
    private UpdateProfileService $updateProfileService;
    private ChangePasswordService $changePasswordService;
    private RequestForgotPasswordService $requestForgotPasswordService;
    private ResetPasswordService $resetPasswordService;
    private FetchUsersAsAdminService $fetchUsersAsAdminService;
    private CreateService $createService;
    private UpdateUserService $updateUserService;
    private DeleteService $deleteService;
    private GetWalletBalanceService $getWalletBalanceService;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        UserRepositoryInterface $userRepository,
        UpdateProfileService $updateProfileService,
        ChangePasswordService $changePasswordService,
        RequestForgotPasswordService $requestForgotPasswordService,
        ResetPasswordService $resetPasswordService,
        FetchUsersAsAdminService $fetchUsersAsAdminService,
        CreateService $createService,
        UpdateUserService $updateUserService,
        DeleteService $deleteService,
        GetWalletBalanceService $getWalletBalanceService
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->userRepository = $userRepository;
        $this->updateProfileService = $updateProfileService;
        $this->changePasswordService = $changePasswordService;
        $this->requestForgotPasswordService = $requestForgotPasswordService;
        $this->resetPasswordService = $resetPasswordService;
        $this->fetchUsersAsAdminService = $fetchUsersAsAdminService;
        $this->createService = $createService;
        $this->updateUserService = $updateUserService;
        $this->deleteService = $deleteService;
        $this->getWalletBalanceService = $getWalletBalanceService;
    }

    public function profile()
    {
        $user = $this->apiCredentialService->getAuthUser();
        View::render('user/profile', [
            'title' => 'Profile',
            'subtitle' => 'Account details',
            'nav' => 'profile',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function updateProfile()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        try {
            $this->updateProfileService->execute(new UpdateProfileDto(
                (int) $authUser->id,
                (string) $this->request->get('name'),
                (string) $this->request->get('phone'),
                (string) $this->request->get('delivery_address')
            ));
            WebSession::flash('success', 'Profile updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/profile');
    }

    public function changePassword()
    {
        $authUser = $this->apiCredentialService->getAuthUser();
        try {
            $this->changePasswordService->execute(new ChangePasswordDto(
                (int) $authUser->id,
                (string) $this->request->get('old_password'),
                (string) $this->request->get('new_password'),
                (string) $this->request->get('confirm_password')
            ));
            WebSession::flash('success', 'Password changed.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/profile');
    }

    public function showForgotPassword()
    {
        View::render('auth/forgot-password', [
            'title' => 'Forgot password',
            'flash' => WebSession::pullFlash(),
            'email' => '',
        ], 'layouts/guest');
    }

    public function forgotPassword()
    {
        $email = trim((string) $this->request->get('email', ''));
        try {
            $this->requestForgotPasswordService->execute(new RequestForgotPasswordDto($email));
            WebSession::flash('success', 'If that email exists, a reset OTP was sent.');
            WebSession::redirect('/reset-password?email=' . urlencode($email));
        } catch (\Exception $e) {
            View::render('auth/forgot-password', [
                'title' => 'Forgot password',
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'email' => $email,
            ], 'layouts/guest');
        }
    }

    public function showResetPassword()
    {
        View::render('auth/reset-password', [
            'title' => 'Reset password',
            'flash' => WebSession::pullFlash(),
            'email' => trim((string) $this->request->get('email', '')),
        ], 'layouts/guest');
    }

    public function resetPassword()
    {
        $email = trim((string) $this->request->get('email', ''));
        try {
            $this->resetPasswordService->execute(new ResetPasswordDto(
                $email,
                (string) $this->request->get('otp', ''),
                (string) $this->request->get('password', ''),
                (string) $this->request->get('confirm_password', '')
            ));
            WebSession::flash('success', 'Password reset. You can sign in now.');
            WebSession::redirect('/login');
        } catch (\Exception $e) {
            View::render('auth/reset-password', [
                'title' => 'Reset password',
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'email' => $email,
            ], 'layouts/guest');
        }
    }

    public function adminUsers()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $users = $this->fetchUsersAsAdminService->query([])->fetchAll();
        View::render('admin/users', [
            'title' => 'Users',
            'subtitle' => 'Accounts',
            'nav' => 'admin-users',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'users' => is_array($users) ? $users : [],
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function adminUserShow()
    {
        $admin = $this->apiCredentialService->getAuthUser();
        $userId = (int) $this->request->get('user_id');
        $target = $this->userRepository->find($userId);
        Contracts::requireEntityFound($target, 'User');
        View::render('admin/user-show', [
            'title' => $target->name,
            'subtitle' => $target->email,
            'nav' => 'admin-users',
            'layout_nav' => 'admin',
            'user' => $admin,
            'balance' => $this->getWalletBalanceService->query((int) $admin->id),
            'target' => $target,
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function adminUserUpdate()
    {
        $userId = (int) $this->request->get('user_id');
        try {
            $this->updateUserService->execute(new UpdateUserDto(
                $userId,
                (string) $this->request->get('name'),
                (string) $this->request->get('phone'),
                (string) $this->request->get('delivery_address'),
                (string) $this->request->get('social_security_number', ''),
                (string) $this->request->get('role'),
                (string) $this->request->get('status'),
                (string) $this->request->get('country_code', '+234')
            ));
            WebSession::flash('success', 'User updated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/users/' . $userId);
    }

    public function adminUserCreateForm()
    {
        $admin = $this->apiCredentialService->getAuthUser();
        View::render('admin/user-create', [
            'title' => 'Create user',
            'subtitle' => 'New account',
            'nav' => 'admin-users',
            'layout_nav' => 'admin',
            'user' => $admin,
            'balance' => $this->getWalletBalanceService->query((int) $admin->id),
            'flash' => WebSession::pullFlash(),
            'old' => [],
        ]);
    }

    public function adminUserCreate()
    {
        try {
            $created = $this->createService->execute(new CreateDto(
                (string) $this->request->get('email'),
                (string) $this->request->get('password'),
                (string) $this->request->get('name'),
                (string) $this->request->get('phone'),
                (string) $this->request->get('delivery_address'),
                (string) $this->request->get('social_security_number', ''),
                (string) $this->request->get('role', 'customer'),
                (string) $this->request->get('status', 'active'),
                (string) $this->request->get('country_code', '+234')
            ));
            WebSession::flash('success', 'User created.');
            WebSession::redirect('/admin/users/' . $created->id);
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
            WebSession::redirect('/admin/users/create');
        }
    }

    public function adminUserDelete()
    {
        $userId = (int) $this->request->get('user_id');
        try {
            $this->deleteService->execute($userId);
            WebSession::flash('success', 'User deleted.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/users');
    }

}
