<?php 
namespace Application\MailNotifications\Wallet;

interface WalletNotificationServiceInterface {
    public function sendManualTopUpNotificationToAdmin(string $admin_email,int $wallet_id);
    public function sendManualTopUpNotificationToUser(int $wallet_id);
    public function sendApproveManualTopUpNotificationToUser(int $wallet_id);
    public function sendRejectManualTopUpNotificationToUser(int $wallet_id);
}