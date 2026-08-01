<?php

use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Application\MailNotifications\AccountMailNotificationService;
use Application\MailNotifications\AccountMailNotificationServiceInterface;
use Application\MailNotifications\Wallet\WalletNotificationService;
use Application\MailNotifications\Wallet\WalletNotificationServiceInterface;
use Application\Notifications\NotificationMigrationInterface;
use Application\Notifications\NotificationService;
use Application\Notifications\NotificationServiceInterface;
use Application\PlatformConfig\PlatformConfigMigrationServiceInterface;
use Application\PlatformConfig\PlatformConfigService;
use Application\PlatformConfig\PlatformConfigServiceInterface;
use Application\User\UserMigrationServiceInterface;
use Application\User\UserService;
use Application\User\UserServiceInterface;
use Application\User\UserValidationService;
use Application\User\UserValidationServiceInterface;
use Application\Wallet\WalletMigrationServiceInterface;
use Application\Wallet\WalletService;
use Application\Wallet\WalletServiceInterface;
use Application\Wallet\WalletValidationService;
use Application\Wallet\WalletValidationServiceInterface;
use Domain\Notifications\NotificationRepositoryInterface;
use Domain\PlatformConfig\PlatformConfigRepositoryInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Wallet\WalletRepositoryInterface;
use Infrastructure\Notification\NotificationRepository;
use Infrastructure\Notification\NotificationMigration;
use Infrastructure\PlatformConfig\PlatformConfigMigrationService;
use Infrastructure\PlatformConfig\PlatformConfigRepository;
use Infrastructure\User\UserMigrationService;
use Infrastructure\User\UserRepository;
use Infrastructure\Wallet\WalletMigrationService;
use Infrastructure\Wallet\WalletRepository;
use Presentation\ApiCredential\ApiCredentialService;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\Http\Controllers\WalletController;
use Presentation\Http\Middlewares\WalletFeedbackMiddleware;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

/**
 * @var AppServiceContainer $appServiceContainer
 */

$appServiceContainer->container()->set(UserServiceInterface::class, function () use ($appServiceContainer) {
    return new UserService(
        $appServiceContainer->container()->get(UserMigrationServiceInterface::class),
        $appServiceContainer->container()->get(UserValidationServiceInterface::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class),
        $appServiceContainer->container()->get(AccountMailNotificationServiceInterface::class),
        $appServiceContainer->container()->get(NotificationServiceInterface::class)
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


$appServiceContainer->container()->set(WalletValidationServiceInterface::class, function () use ($appServiceContainer) {
    return new WalletValidationService();
});

$appServiceContainer->container()->set(WalletServiceInterface::class, function () use ($appServiceContainer) {
    return new WalletService(
        $appServiceContainer->container()->get(WalletValidationServiceInterface::class),
        $appServiceContainer->container()->get(WalletRepositoryInterface::class),
        $appServiceContainer->container()->get(WalletNotificationServiceInterface::class),
        $appServiceContainer->container()->get(EnvServiceInterface::class),
        $appServiceContainer->container()->get(PaymentServiceInterface::class),
        $appServiceContainer->container()->get(UserRepositoryInterface::class),
        $appServiceContainer->container()->get(FileUploadServiceInterface::class),
        $appServiceContainer->container()->get(WalletMigrationServiceInterface::class),
        $appServiceContainer->container()->get(UserServiceInterface::class)
    );
});


$appServiceContainer->container()->set(WalletController::class, function () use ($appServiceContainer) {
    return new WalletController(
        $appServiceContainer->container()->get(WalletServiceInterface::class),
        $appServiceContainer->container()->get(Request::class),
        $appServiceContainer->container()->get(JsonResponseServiceInterface::class),
        $appServiceContainer->container()->get(ApiCredentialServiceInterface::class),
        $appServiceContainer->container()->get(WalletRepositoryInterface::class)
    );
});

$appServiceContainer->container()->set(WalletFeedbackMiddleware::class, function () use ($appServiceContainer) {
    return new WalletFeedbackMiddleware(
        $appServiceContainer->container()->get(ApiCredentialServiceInterface::class),
        $appServiceContainer->container()->get(PaymentServiceInterface::class),
        $appServiceContainer->container()->get(WalletServiceInterface::class)
    );
});


$appServiceContainer->container()->set(NotificationMigrationInterface::class, function () use ($appServiceContainer) {
    return new NotificationMigration(
        $appServiceContainer->container()->get(Migration::class)
    );
});

$appServiceContainer->container()->set(NotificationRepositoryInterface::class, function () use ($appServiceContainer) {
    return new NotificationRepository(
        $appServiceContainer->container()->get(DbServiceInterface::class),
        $appServiceContainer->container()->get(QueryBuilderServiceInterface::class)
    );
});

// NotificationServiceInterface
$appServiceContainer->container()->set(NotificationServiceInterface::class, function () use ($appServiceContainer) {
    return new NotificationService(
        $appServiceContainer->container()->get(NotificationRepositoryInterface::class),
        $appServiceContainer->container()->get(NotificationMigrationInterface::class)
    );
});

$appServiceContainer->container()->set(PlatformConfigRepositoryInterface::class, function () use ($appServiceContainer) {
    return new PlatformConfigRepository(
        $appServiceContainer->container()->get(DbServiceInterface::class),
        $appServiceContainer->container()->get(QueryBuilderServiceInterface::class)
    );
});

$appServiceContainer->container()->set(PlatformConfigServiceInterface::class, function () use ($appServiceContainer) {
    return new PlatformConfigService(
        $appServiceContainer->container()->get(PlatformConfigRepositoryInterface::class),
        $appServiceContainer->container()->get(PlatformConfigMigrationServiceInterface::class)
    );
});

$appServiceContainer->container()->set(PlatformConfigMigrationServiceInterface::class, function () use ($appServiceContainer) {
    return new PlatformConfigMigrationService(
        $appServiceContainer->container()->get(Migration::class)
    );
});

