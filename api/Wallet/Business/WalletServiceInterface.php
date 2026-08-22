<?php

namespace Wallet\Business;

use Shared\AbstractBaseServiceInterface;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\LogDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Data\WalletEntity;

/**
 * @extends AbstractBaseServiceInterface<WalletEntity>
 */
interface WalletServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * @param TopUpOnlineDto $topUpOnlineDto
     * @return WalletEntity
     */
    public function topUpOnline(TopUpOnlineDto $topUpOnlineDto);

    /**
     * @param LogDto $logDto
     * @return WalletEntity
     */
    public function log(LogDto $logDto);

    /**
     * @param TopUpManualDto $topUpManualDto
     * @return WalletEntity
     */
    public function topUpManual(TopUpManualDto $topUpManualDto);

    /**
     * @param ApproveManualTopUpDto $approveManualTopUpDto
     * @return WalletEntity
     */
    public function approveManualTopUp(ApproveManualTopUpDto $approveManualTopUpDto);

    /**
     * @param RejectManualTopUpDto $rejectManualTopUpDto
     * @return WalletEntity
     */
    public function rejectManualTopUp(RejectManualTopUpDto $rejectManualTopUpDto);

    public function migrate();

    public function onlinePendingForUser(int $user_id);
}
