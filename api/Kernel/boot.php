<?php

use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Application\MailNotifications\AccountMailNotificationService;
use Application\MailNotifications\AccountMailNotificationServiceInterface;
use Application\MailNotifications\Base\BaseMailTheme;
use Application\MailNotifications\Base\BaseMailThemeInterface;
use Application\MailNotifications\ProxyOrderMailNotification;
use Application\MailNotifications\ProxyOrderMailNotificationInterface;
use Application\MailNotifications\Wallet\WalletNotificationService;
use Application\MailNotifications\Wallet\WalletNotificationServiceInterface;
use Application\Notifications\NotificationMigrationInterface;
use Application\Notifications\NotificationService;
use Application\Notifications\NotificationServiceInterface;
use Application\PlatformConfig\PlatformConfigMigrationServiceInterface;
use Application\PlatformConfig\PlatformConfigService;
use Application\PlatformConfig\PlatformConfigServiceInterface;
use Application\ProxyOrder\ProxyOrderMigrationServiceInterface;
use Application\ProxyOrder\ProxyOrderService;
use Application\ProxyOrder\ProxyOrderServiceInterface;
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
use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use Domain\User\UserRepositoryInterface;
use Domain\Wallet\WalletRepositoryInterface;
use Infrastructure\Notification\NotificationRepository;
use Infrastructure\Notification\NotificationMigration;
use Infrastructure\PlatformConfig\PlatformConfigMigrationService;
use Infrastructure\PlatformConfig\PlatformConfigRepository;
use Infrastructure\ProxyOrder\ProxyOrderMigrationService;
use Infrastructure\ProxyOrder\ProxyOrderRepository;
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


$appServiceContainer->container()->map(UserServiceInterface::class, UserService::class);

$appServiceContainer->container()->map(UserMigrationServiceInterface::class, UserMigrationService::class);

$appServiceContainer->container()->map(UserValidationServiceInterface::class, UserValidationService::class);

$appServiceContainer->container()->map(UserRepositoryInterface::class, UserRepository::class);

$appServiceContainer->container()->map(AccountMailNotificationServiceInterface::class, AccountMailNotificationService::class);

$appServiceContainer->container()->singleton(ApiCredentialServiceInterface::class, ApiCredentialService::class);

$appServiceContainer->container()->map(WalletMigrationServiceInterface::class, WalletMigrationService::class);

$appServiceContainer->container()->map(WalletRepositoryInterface::class, WalletRepository::class);

$appServiceContainer->container()->map(WalletNotificationServiceInterface::class, WalletNotificationService::class);

$appServiceContainer->container()->map(WalletValidationServiceInterface::class, WalletValidationService::class);

$appServiceContainer->container()->map(WalletServiceInterface::class, WalletService::class);

$appServiceContainer->container()->map(NotificationMigrationInterface::class, NotificationMigration::class);

$appServiceContainer->container()->map(NotificationRepositoryInterface::class, NotificationRepository::class);

$appServiceContainer->container()->map(NotificationServiceInterface::class, NotificationService::class);

$appServiceContainer->container()->map(PlatformConfigRepositoryInterface::class, PlatformConfigRepository::class);

$appServiceContainer->container()->map(PlatformConfigServiceInterface::class, PlatformConfigService::class);

$appServiceContainer->container()->map(PlatformConfigMigrationServiceInterface::class, PlatformConfigMigrationService::class);

$appServiceContainer->container()->map(ProxyOrderServiceInterface::class, ProxyOrderService::class);

$appServiceContainer->container()->map(ProxyOrderRepositoryInterface::class, ProxyOrderRepository::class);

$appServiceContainer->container()->map(ProxyOrderMailNotificationInterface::class, ProxyOrderMailNotification::class);

$appServiceContainer->container()->map(ProxyOrderMigrationServiceInterface::class, ProxyOrderMigrationService::class);

$appServiceContainer->container()->singleton(BaseMailThemeInterface::class, BaseMailTheme::class);