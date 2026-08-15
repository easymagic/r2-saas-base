<?php

namespace EcomOrder\Business;

use Shared\AbstractBaseServiceInterface;
use EcomOrder\Data\EcomOrderEntity;

/**
 * @extends AbstractBaseServiceInterface<EcomOrderEntity>
 */
interface EcomOrderServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
}
