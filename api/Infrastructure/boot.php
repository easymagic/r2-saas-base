<?php

use App\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Application\MailNotifications\AccountMailNotificationService;
use Application\MailNotifications\AccountMailNotificationServiceInterface;
use Application\MailNotifications\Wallet\WalletNotificationService;
use Application\MailNotifications\Wallet\WalletNotificationServiceInterface;
use Application\User\UserMigrationServiceInterface;
use Application\User\UserService;
use Application\User\UserServiceInterface;
use Application\User\UserValidationService;
use Application\User\UserValidationServiceInterface;
use Application\Wallet\WalletMigrationServiceInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Wallet\WalletRepositoryInterface;
use Infrastructure\User\UserMigrationService;
use Infrastructure\User\UserRepository;
use Infrastructure\Wallet\WalletMigrationService;
use Infrastructure\Wallet\WalletRepository;
use Presentation\ApiCredential\ApiCredentialService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */

$appServiceContainer->container()->set(UserServiceInterface::class, function () use ($appServiceContainer) {
    return new UserService(
        $appServiceContainer->container()->get(UserMigrationServiceInterface::class),
        $appServiceContainer->container()->get(UserValidationServiceInterface::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class),
        $appServiceContainer->container()->get(AccountMailNotificationServiceInterface::class)
    );
});


$appServiceContainer->container()->set(UserMigrationServiceInterface::class, function () use ($appServiceContainer) {
    return new UserMigrationService(
        $appServiceContainer->container()->get(Migration::class)
    );
});

$appServiceContainer->container()->set(UserValidationServiceInterface::class, function () use ($appServiceContainer) {
    return new UserValidationService(
        $appServiceContainer->container()->get(UserRepositoryInterface::class)
    );
});

$appServiceContainer->container()->set(UserRepositoryInterface::class, function () use ($appServiceContainer) {
    return new UserRepository(
        $appServiceContainer->container()->get(DbServiceInterface::class),
        $appServiceContainer->container()->get(QueryBuilderServiceInterface::class)
    );
});

$appServiceContainer->container()->set(AccountMailNotificationServiceInterface::class, function () use ($appServiceContainer) {
    return new AccountMailNotificationService(
        $appServiceContainer->container()->get(MailServiceInterface::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class)
    );
});


$appServiceContainer->container()->set(ApiCredentialServiceInterface::class, function () use ($appServiceContainer) {
    return new ApiCredentialService(
        $appServiceContainer->container()->get(Request::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class),
        $appServiceContainer->container()->get(EnvServiceInterface::class)
    );
});


$appServiceContainer->container()->set(WalletMigrationServiceInterface::class, function () use ($appServiceContainer) {
    return new WalletMigrationService(
        $appServiceContainer->container()->get(Migration::class)
    );
});

$appServiceContainer->container()->set(WalletRepositoryInterface::class, function () use ($appServiceContainer) {
    return new WalletRepository(
        $appServiceContainer->container()->get(QueryBuilderServiceInterface::class),
        $appServiceContainer->container()->get(DbServiceInterface::class)
    );
});

$appServiceContainer->container()->set(WalletNotificationServiceInterface::class, function () use ($appServiceContainer) {
    return new WalletNotificationService(
        $appServiceContainer->container()->get(MailServiceInterface::class),
        $appServiceContainer->container()->get(WalletRepositoryInterface::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class)
    );
});