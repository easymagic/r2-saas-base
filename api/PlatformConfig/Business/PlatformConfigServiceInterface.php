<?php

namespace PlatformConfig\Business;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Data\PlatformConfigEntity;
use Shared\AbstractBaseServiceInterface;

/**
 * Platform Config Service Interface
 * @extends AbstractBaseServiceInterface<PlatformConfigEntity>
 */
interface PlatformConfigServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * @param string $setting
     * @return mixed
     */
    function get(string $setting, mixed $default = null);

    /**
     * @param SetDto $setDto
     * @return PlatformConfigEntity
     */
    function set(SetDto $setDto);

    /**
     * @return array
     */
    function getAll();

    /**
     * @return mixed
     */
    function migrate();
}
