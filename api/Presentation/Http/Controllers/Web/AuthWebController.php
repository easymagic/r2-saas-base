<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Dtos\LoginDto;
use User\Business\Dtos\RegisterDto;
use User\Business\Dtos\VerifyEmailDto;
use User\Business\Usecases\LoginService;
use User\Business\Usecases\LogoutService;
use User\Business\Usecases\RegisterService;
use User\Business\Usecases\VerifyEmailService;

class AuthWebController
{
    private LoginService $loginService;
    private LogoutService $logoutService;
    private RegisterService $registerService;
    private VerifyEmailService $verifyEmailService;
    private Request $request;
    private ApiCredentialServiceInterface $apiCredentialService;

    public function __construct(
        LoginService $loginService,
        LogoutService $logoutService,
        RegisterService $registerService,
        VerifyEmailService $verifyEmailService,
        Request $request,
        ApiCredentialServiceInterface $apiCredentialService
    ) {
        $this->loginService = $loginService;
        $this->logoutService = $logoutService;
        $this->registerService = $registerService;
        $this->verifyEmailService = $verifyEmailService;
        $this->request = $request;
        $this->apiCredentialService = $apiCredentialService;
    }

    public function home()
    {
        if (WebSession::isLoggedIn()) {
            try {
                WebSession::bindAuth($this->apiCredentialService);
                WebSession::redirect('/dashboard');
            } catch (\Exception $e) {
                WebSession::logout();
            }
        }
        View::render('home/index', [
            'title' => '',
            'flash' => WebSession::pullFlash(),
        ], 'layouts/guest');
    }

    public function showLogin()
    {
        if (WebSession::isLoggedIn()) {
            WebSession::redirect('/dashboard');
        }
        View::render('auth/login', [
            'title' => 'Sign in',
            'flash' => WebSession::pullFlash(),
            'email' => '',
        ], 'layouts/guest');
    }

    public function login()
    {
        $email = trim((string) $this->request->get('email', ''));
        $password = (string) $this->request->get('password', '');
        try {
            $user = $this->loginService->execute(new LoginDto($email, $password));
            WebSession::login($user);
            WebSession::flash('success', 'Welcome back, ' . $user->name . '.');
            if ($user->isAdmin()) {
                WebSession::redirect('/admin');
            }
            WebSession::redirect('/dashboard');
        } catch (\Exception $e) {
            View::render('auth/login', [
                'title' => 'Sign in',
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'email' => $email,
            ], 'layouts/guest');
        }
    }

    public function showRegister()
    {
        if (WebSession::isLoggedIn()) {
            WebSession::redirect('/dashboard');
        }
        View::render('auth/register', [
            'title' => 'Create account',
            'flash' => WebSession::pullFlash(),
            'old' => [],
        ], 'layouts/guest');
    }

    public function register()
    {
        $old = [
            'name' => trim((string) $this->request->get('name', '')),
            'email' => trim((string) $this->request->get('email', '')),
            'phone' => trim((string) $this->request->get('phone', '')),
            'country_code' => trim((string) $this->request->get('country_code', '+234')),
            'delivery_address' => trim((string) $this->request->get('delivery_address', '')),
            'social_security_number' => trim((string) $this->request->get('social_security_number', '')),
        ];
        try {
            $dto = new RegisterDto(
                $old['email'],
                (string) $this->request->get('password', ''),
                $old['name'],
                $old['phone'],
                $old['delivery_address'],
                $old['social_security_number'],
                'customer'
            );
            $dto->country_code = $old['country_code'];
            $user = $this->registerService->execute($dto);
            WebSession::flash('success', 'Account created. Enter the OTP sent to your email.');
            WebSession::redirect('/register/verify-otp?email=' . urlencode($user->email));
        } catch (\Exception $e) {
            View::render('auth/register', [
                'title' => 'Create account',
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'old' => $old,
            ], 'layouts/guest');
        }
    }

    public function showVerifyOtp()
    {
        View::render('auth/verify-otp', [
            'title' => 'Verify email',
            'flash' => WebSession::pullFlash(),
            'email' => trim((string) $this->request->get('email', '')),
        ], 'layouts/guest');
    }

    public function verifyOtp()
    {
        $email = trim((string) $this->request->get('email', ''));
        $otp = trim((string) $this->request->get('otp', ''));
        try {
            $this->verifyEmailService->execute(new VerifyEmailDto($email, $otp));
            WebSession::flash('success', 'Email verified. You can sign in now.');
            WebSession::redirect('/login');
        } catch (\Exception $e) {
            View::render('auth/verify-otp', [
                'title' => 'Verify email',
                'flash' => ['type' => 'error', 'message' => $e->getMessage()],
                'email' => $email,
            ], 'layouts/guest');
        }
    }

    public function logout()
    {
        try {
            if (WebSession::isLoggedIn()) {
                WebSession::bindAuth($this->apiCredentialService);
                $user = $this->apiCredentialService->getAuthUser();
                $this->logoutService->execute((int) $user->id);
            }
        } catch (\Exception $e) {
            // ignore
        }
        WebSession::logout();
        WebSession::flash('success', 'Signed out.');
        WebSession::redirect('/login');
    }
}
