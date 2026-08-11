<?php

namespace UserKyc\Business;

use Shared\AbstractBaseServiceInterface;
use UserKyc\Data\UserKycEntity;

/**
 * @extends AbstractBaseServiceInterface<UserKycEntity>
 */
interface UserKycServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
}
