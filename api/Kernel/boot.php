<?php

use R2Packages\Framework\Infrastructure\Framework\Container\AppServiceContainer;

/**
 * @var AppServiceContainer $appServiceContainer
 */

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\ApiCredential\ApiCredentialService;

/* 
 * Wallet
 */
use Wallet\Business\WalletServiceInterface;
use Wallet\Business\WalletService;
use Wallet\Business\WalletValidationServiceInterface;
use Wallet\Business\WalletValidationService;
use Wallet\Business\WalletNotificationServiceInterface;
use Wallet\Business\WalletNotificationService;
use Wallet\Data\WalletRepositoryInterface;
use Wallet\Data\WalletRepository;
use Wallet\Data\WalletMigrationRepositoryInterface;
use Wallet\Data\WalletMigrationRepository;

/*
 * Notification
 */
use Notification\Business\NotificationServiceInterface;
use Notification\Business\NotificationService;
use Notification\Data\NotificationRepositoryInterface;
use Notification\Data\NotificationRepository;
use Notification\Data\NotificationMigrationRepositoryInterface;
use Notification\Data\NotificationMigrationRepository;

/*
 * Platform Config
 */
use PlatformConfig\Business\PlatformConfigServiceInterface;
use PlatformConfig\Business\PlatformConfigService;
use PlatformConfig\Data\PlatformConfigRepositoryInterface;
use PlatformConfig\Data\PlatformConfigRepository;
use PlatformConfig\Data\PlatformConfigMigrationRepositoryInterface;
use PlatformConfig\Data\PlatformConfigMigrationRepository;

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

/*
 * SnappyOrder
 */
use SnappyOrder\Business\SnappyOrderMailService;
use SnappyOrder\Business\SnappyOrderMailServiceInterface;
use SnappyOrder\Business\SnappyOrderService;
use SnappyOrder\Business\SnappyOrderServiceInterface;
use SnappyOrder\Data\SnappyOrderMigrationRepository;
use SnappyOrder\Data\SnappyOrderMigrationRepositoryInterface;
use SnappyOrder\Data\SnappyOrderRepository;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

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

$appServiceContainer->container()->map(SnappyOrderServiceInterface::class, SnappyOrderService::class);
$appServiceContainer->container()->map(SnappyOrderMailServiceInterface::class, SnappyOrderMailService::class);
$appServiceContainer->container()->map(SnappyOrderMigrationRepositoryInterface::class, SnappyOrderMigrationRepository::class);
$appServiceContainer->container()->map(SnappyOrderRepositoryInterface::class, SnappyOrderRepository::class);

$appServiceContainer->container()->singleton(BaseMailThemeInterface::class, BaseMailTheme::class);

