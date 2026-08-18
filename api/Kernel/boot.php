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

/*
 * ProxyOrderChangeLog
 */
use ProxyOrderChangeLog\Business\ProxyOrderChangeLogService;
use ProxyOrderChangeLog\Business\ProxyOrderChangeLogServiceInterface;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogMigrationRepository;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogMigrationRepositoryInterface;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogRepository;
use ProxyOrderChangeLog\Data\ProxyOrderChangeLogRepositoryInterface;

/*
 * Batch
 */
use Batch\Business\BatchService;
use Batch\Business\BatchServiceInterface;
use Batch\Data\BatchMigrationRepository;
use Batch\Data\BatchMigrationRepositoryInterface;
use Batch\Data\BatchRepository;
use Batch\Data\BatchRepositoryInterface;

/*
 * Thread
 */
use Thread\Business\ThreadNotificationService;
use Thread\Business\ThreadNotificationServiceInterface;
use Thread\Business\ThreadService;
use Thread\Business\ThreadServiceInterface;
use Thread\Data\ThreadMigrationRepository;
use Thread\Data\ThreadMigrationRepositoryInterface;
use Thread\Data\ThreadRepository;
use Thread\Data\ThreadRepositoryInterface;

/*
 * Log
 */
use Log\Business\LogService;
use Log\Business\LogServiceInterface;
use Log\Data\LogMigrationRepository;
use Log\Data\LogMigrationRepositoryInterface;
use Log\Data\LogRepository;
use Log\Data\LogRepositoryInterface;

/*
 * Category
 */
use Category\Business\CategoryService;
use Category\Business\CategoryServiceInterface;
use Category\Data\CategoryMigrationRepository;
use Category\Data\CategoryMigrationRepositoryInterface;
use Category\Data\CategoryRepository;
use Category\Data\CategoryRepositoryInterface;

/*
 * Product
 */
use Product\Business\ProductService;
use Product\Business\ProductServiceInterface;
use Product\Data\ProductMigrationRepository;
use Product\Data\ProductMigrationRepositoryInterface;
use Product\Data\ProductRepository;
use Product\Data\ProductRepositoryInterface;

/*
 * Cart
 */
use Cart\Business\CartService;
use Cart\Business\CartServiceInterface;
use Cart\Data\CartMigrationRepository;
use Cart\Data\CartMigrationRepositoryInterface;
use Cart\Data\CartRepository;
use Cart\Data\CartRepositoryInterface;

/*
 * EcomOrder
 */
use EcomOrder\Business\EcomOrderService;
use EcomOrder\Business\EcomOrderServiceInterface;
use EcomOrder\Business\EcomOrderNotificationService;
use EcomOrder\Business\EcomOrderNotificationServiceInterface;
use EcomOrder\Data\EcomOrderMigrationRepository;
use EcomOrder\Data\EcomOrderMigrationRepositoryInterface;
use EcomOrder\Data\EcomOrderRepository;
use EcomOrder\Data\EcomOrderRepositoryInterface;

/*
 * OrderItem
 */
use OrderItem\Business\OrderItemService;
use OrderItem\Business\OrderItemServiceInterface;
use OrderItem\Business\OrderItemNotificationService;
use OrderItem\Business\OrderItemNotificationServiceInterface;
use OrderItem\Data\OrderItemMigrationRepository;
use OrderItem\Data\OrderItemMigrationRepositoryInterface;
use OrderItem\Data\OrderItemRepository;
use OrderItem\Data\OrderItemRepositoryInterface;

/*
 * BnplPaymentSchedule
 */
use BnplPaymentSchedule\Business\BnplPaymentScheduleService;
use BnplPaymentSchedule\Business\BnplPaymentScheduleServiceInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepository;
use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepositoryInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepository;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;

/*
 * UserKyc
 */
use UserKyc\Business\UserKycService;
use UserKyc\Business\UserKycServiceInterface;
use UserKyc\Business\UserKycNotificationService;
use UserKyc\Business\UserKycNotificationServiceInterface;
use UserKyc\Data\UserKycMigrationRepository;
use UserKyc\Data\UserKycMigrationRepositoryInterface;
use UserKyc\Data\UserKycRepository;
use UserKyc\Data\UserKycRepositoryInterface;

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

$appServiceContainer->container()->map(ProxyOrderChangeLogServiceInterface::class, ProxyOrderChangeLogService::class);
$appServiceContainer->container()->map(ProxyOrderChangeLogMigrationRepositoryInterface::class, ProxyOrderChangeLogMigrationRepository::class);
$appServiceContainer->container()->map(ProxyOrderChangeLogRepositoryInterface::class, ProxyOrderChangeLogRepository::class);

$appServiceContainer->container()->map(BatchServiceInterface::class, BatchService::class);
$appServiceContainer->container()->map(BatchMigrationRepositoryInterface::class, BatchMigrationRepository::class);
$appServiceContainer->container()->map(BatchRepositoryInterface::class, BatchRepository::class);

$appServiceContainer->container()->map(ThreadServiceInterface::class, ThreadService::class);
$appServiceContainer->container()->map(ThreadNotificationServiceInterface::class, ThreadNotificationService::class);
$appServiceContainer->container()->map(ThreadMigrationRepositoryInterface::class, ThreadMigrationRepository::class);
$appServiceContainer->container()->map(ThreadRepositoryInterface::class, ThreadRepository::class);

$appServiceContainer->container()->map(LogServiceInterface::class, LogService::class);
$appServiceContainer->container()->map(LogMigrationRepositoryInterface::class, LogMigrationRepository::class);
$appServiceContainer->container()->map(LogRepositoryInterface::class, LogRepository::class);

$appServiceContainer->container()->map(CategoryServiceInterface::class, CategoryService::class);
$appServiceContainer->container()->map(CategoryMigrationRepositoryInterface::class, CategoryMigrationRepository::class);
$appServiceContainer->container()->map(CategoryRepositoryInterface::class, CategoryRepository::class);

$appServiceContainer->container()->map(ProductServiceInterface::class, ProductService::class);
$appServiceContainer->container()->map(ProductMigrationRepositoryInterface::class, ProductMigrationRepository::class);
$appServiceContainer->container()->map(ProductRepositoryInterface::class, ProductRepository::class);

$appServiceContainer->container()->map(CartServiceInterface::class, CartService::class);
$appServiceContainer->container()->map(CartMigrationRepositoryInterface::class, CartMigrationRepository::class);
$appServiceContainer->container()->map(CartRepositoryInterface::class, CartRepository::class);

$appServiceContainer->container()->map(EcomOrderServiceInterface::class, EcomOrderService::class);
$appServiceContainer->container()->map(EcomOrderNotificationServiceInterface::class, EcomOrderNotificationService::class);
$appServiceContainer->container()->map(EcomOrderMigrationRepositoryInterface::class, EcomOrderMigrationRepository::class);
$appServiceContainer->container()->map(EcomOrderRepositoryInterface::class, EcomOrderRepository::class);

$appServiceContainer->container()->map(OrderItemServiceInterface::class, OrderItemService::class);
$appServiceContainer->container()->map(OrderItemNotificationServiceInterface::class, OrderItemNotificationService::class);
$appServiceContainer->container()->map(OrderItemMigrationRepositoryInterface::class, OrderItemMigrationRepository::class);
$appServiceContainer->container()->map(OrderItemRepositoryInterface::class, OrderItemRepository::class);

$appServiceContainer->container()->map(BnplPaymentScheduleServiceInterface::class, BnplPaymentScheduleService::class);
$appServiceContainer->container()->map(BnplPaymentScheduleMigrationRepositoryInterface::class, BnplPaymentScheduleMigrationRepository::class);
$appServiceContainer->container()->map(BnplPaymentScheduleRepositoryInterface::class, BnplPaymentScheduleRepository::class);

$appServiceContainer->container()->map(UserKycServiceInterface::class, UserKycService::class);
$appServiceContainer->container()->map(UserKycNotificationServiceInterface::class, UserKycNotificationService::class);
$appServiceContainer->container()->map(UserKycMigrationRepositoryInterface::class, UserKycMigrationRepository::class);
$appServiceContainer->container()->map(UserKycRepositoryInterface::class, UserKycRepository::class);

$appServiceContainer->container()->singleton(BaseMailThemeInterface::class, BaseMailTheme::class);

