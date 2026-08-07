<?php 
namespace PlatformConfig\Business;

use PlatformConfig\Data\PlatformConfigEntity;
use Shared\AbstractBaseServiceInterface;


/**
 * Platform Config Service Interface
 * @extends AbstractBaseServiceInterface<PlatformConfigEntity>
 */
interface PlatformConfigServiceInterface extends AbstractBaseServiceInterface {

    /**
     * Get a platform config setting
     * @param string $setting
     * @return string
     */
    function get(string $setting, mixed $default = null);

    /**
     * Set a platform config setting
     * @param string $setting
     * @param string $value
     * @return PlatformConfigEntity
     */
    function set(string $setting, string $value);

    /**
     * Get all platform config settings
     * @return array
     */
    function getAll();

    /**
     * Migrate the platform config settings
     * @return mixed
     */
    function migrate();

    
}