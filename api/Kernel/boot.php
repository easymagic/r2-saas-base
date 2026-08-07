<?php


/**
 * @var AppServiceContainer $appServiceContainer
 */

use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;


use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\ApiCredential\ApiCredentialService;
use Business\Wallet\WalletServiceInterface;
use Business\Wallet\WalletService;
use Business\Wallet\WalletValidationServiceInterface;
use Business\Wallet\WalletValidationService;
use Business\Wallet\WalletNotificationServiceInterface;
use Business\Wallet\WalletNotificationService;
use Data\Wallet\WalletRepositoryInterface;
use Data\Wallet\WalletRepository;
use Data\Wallet\WalletMigrationRepositoryInterface;
use Data\Wallet\WalletMigrationRepository;
use Business\Notifications\NotificationServiceInterface;
use Business\Notifications\NotificationService;
use Data\Notifications\NotificationRepositoryInterface;
use Data\Notifications\NotificationRepository;
use Data\Notifications\NotificationMigrationRepositoryInterface;
use Data\Notifications\NotificationMigrationRepository;
use Business\PlatformConfig\PlatformConfigServiceInterface;
use Business\PlatformConfig\PlatformConfigService;
use Data\PlatformConfig\PlatformConfigRepositoryInterface;
use Data\PlatformConfig\PlatformConfigRepository;
use Data\PlatformConfig\PlatformConfigMigrationRepositoryInterface;
use Data\PlatformConfig\PlatformConfigMigrationRepository;
use Business\ProxyOrder\Order\ProxyOrderServiceInterface;
use Business\ProxyOrder\Order\ProxyOrderService;
use Business\ProxyOrder\Order\ProxyOrderMailNotificationInterface;
use Business\ProxyOrder\Order\ProxyOrderMailNotification;
use Data\ProxyOrder\Order\ProxyOrderRepositoryInterface;
use Data\ProxyOrder\Order\ProxyOrderRepository;
use Data\ProxyOrder\Order\ProxyOrderMigrationRepositoryInterface;
use Data\ProxyOrder\Order\ProxyOrderMigrationRepository;
use Business\MailTheme\BaseMailThemeInterface;
use Business\MailTheme\BaseMailTheme;

use User\Business\AccountMailNotificationService;
use User\Business\AccountMailNotificationServiceInterface;
use User\Business\UserService;
use User\Business\UserServiceInterface;
use User\Business\UserValidationService;
use User\Business\UserValidationServiceInterface;
use User\Data\UserMigrationRepository;
use User\Data\UserMigrationRepositoryInterface;
use User\Data\UserRepository;
use User\Data\UserRepositoryInterface;

$appServiceContainer->container()->map(UserServiceInterface::class, UserService::class);

$appServiceContainer->container()->map(UserMigrationRepositoryInterface::class, UserMigrationRepository::class);

$appServiceContainer->container()->map(UserValidationServiceInterface::class, UserValidationService::class);

$appServiceContainer->container()->map(UserRepositoryInterface::class, UserRepository::class);

$appServiceContainer->container()->map(AccountMailNotificationServiceInterface::class, AccountMailNotificationService::class);

$appServiceContainer->container()->singleton(ApiCredentialServiceInterface::class, ApiCredentialService::class);

$appServiceContainer->container()->map(WalletMigrationRepositoryInterface::class, WalletMigrationRepository::class);

$appServiceContainer->container()->map(WalletRepositoryInterface::class, WalletRepository::class);

$appServiceContainer->container()->map(WalletNotificationServiceInterface::class, WalletNotificationService::class);

$appServiceContainer->container()->map(WalletValidationServiceInterface::class, WalletValidationService::class);

$appServiceContainer->container()->map(WalletServiceInterface::class, WalletService::class);

$appServiceContainer->container()->map(NotificationMigrationRepositoryInterface::class, NotificationMigrationRepository::class);

$appServiceContainer->container()->map(NotificationRepositoryInterface::class, NotificationRepository::class);

$appServiceContainer->container()->map(NotificationServiceInterface::class, NotificationService::class);

$appServiceContainer->container()->map(PlatformConfigRepositoryInterface::class, PlatformConfigRepository::class);

$appServiceContainer->container()->map(PlatformConfigServiceInterface::class, PlatformConfigService::class);

$appServiceContainer->container()->map(PlatformConfigMigrationRepositoryInterface::class, PlatformConfigMigrationRepository::class);

$appServiceContainer->container()->map(ProxyOrderServiceInterface::class, ProxyOrderService::class);

$appServiceContainer->container()->map(ProxyOrderRepositoryInterface::class, ProxyOrderRepository::class);

$appServiceContainer->container()->map(ProxyOrderMailNotificationInterface::class, ProxyOrderMailNotification::class);

$appServiceContainer->container()->map(ProxyOrderMigrationRepositoryInterface::class, ProxyOrderMigrationRepository::class);

$appServiceContainer->container()->singleton(BaseMailThemeInterface::class, BaseMailTheme::class);
