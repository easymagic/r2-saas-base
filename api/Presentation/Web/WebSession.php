<?php

namespace Presentation\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use User\Data\UserEntity;

class WebSession
{
    const KEY_USER_ID = 'web_user_id';
    const KEY_USER_TOKEN = 'web_user_token';
    const KEY_FLASH = 'web_flash';
    const KEY_CART_UUID = 'web_cart_uuid';

    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(UserEntity $user)
    {
        self::start();
        $_SESSION[self::KEY_USER_ID] = (int) $user->id;
        $_SESSION[self::KEY_USER_TOKEN] = (string) $user->token;
    }

    public static function logout()
    {
        self::start();
        unset($_SESSION[self::KEY_USER_ID], $_SESSION[self::KEY_USER_TOKEN]);
    }

    public static function userToken()
    {
        self::start();
        return isset($_SESSION[self::KEY_USER_TOKEN]) ? (string) $_SESSION[self::KEY_USER_TOKEN] : '';
    }

    public static function userId()
    {
        self::start();
        return isset($_SESSION[self::KEY_USER_ID]) ? (int) $_SESSION[self::KEY_USER_ID] : 0;
    }

    public static function isLoggedIn()
    {
        return self::userToken() !== '';
    }

    public static function flash($type, $message)
    {
        self::start();
        $_SESSION[self::KEY_FLASH] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function pullFlash()
    {
        self::start();
        if (empty($_SESSION[self::KEY_FLASH])) {
            return null;
        }
        $flash = $_SESSION[self::KEY_FLASH];
        unset($_SESSION[self::KEY_FLASH]);
        return $flash;
    }

    /**
     * Bind session identity into ApiCredentialService for in-process usecases.
     */
    public static function bindAuth(ApiCredentialServiceInterface $apiCredentialService)
    {
        $token = self::userToken();
        if ($token === '') {
            throw new \Exception('Not authenticated');
        }
        $apiCredentialService->validateUserToken($token);
    }

    public static function cartUuid()
    {
        self::start();
        return isset($_SESSION[self::KEY_CART_UUID]) ? (string) $_SESSION[self::KEY_CART_UUID] : '';
    }

    public static function setCartUuid($uuid)
    {
        self::start();
        $_SESSION[self::KEY_CART_UUID] = (string) $uuid;
    }

    public static function redirect($path)
    {
        $path = (string) $path;
        // Reject open redirects / relative targets (relative "shop" from /cart/add → /cart/shop).
        if ($path === '' || (isset($path[0]) && $path[0] !== '/') || strpos($path, '//') !== false) {
            $path = '/';
        }
        $base = web_base_path();
        $path = '/' . ltrim($path, '/');
        if ($path === '/') {
            $url = $base === '' ? '/' : $base . '/';
        } else {
            $url = $base . $path;
        }
        header('Location: ' . $url);
        exit;
    }
}
