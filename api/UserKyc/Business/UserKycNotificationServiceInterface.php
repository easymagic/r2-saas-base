<?php 

namespace UserKyc\Business;

use Shared\AbstractBaseServiceInterface;
use UserKyc\Data\UserKycEntity;

/**
 * @extends AbstractBaseServiceInterface<UserKycEntity>
 */
interface UserKycNotificationServiceInterface extends AbstractBaseServiceInterface
{
    public function sendApproveNotification(int $id);
    public function sendRejectNotification(int $id);
}