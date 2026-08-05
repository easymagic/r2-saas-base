<?php 
namespace Data\PlatformConfig;

interface PlatformConfigRepositoryInterface
{
    /**
     * Find a platform config setting by id
     * @param int $id
     * @return PlatformConfigEntity
     */
    public function find(int $id);

    /**
     * Find a platform config setting by setting
     * @param string $setting
     * @return PlatformConfigEntity
     */
    public function findBySetting(string $setting);

    /**
     * Save a platform config setting
     * @param int $id
     * @param array $data
     * @return PlatformConfigEntity
     */
    public function save(int $id, array $data);

    public function delete(int $id);

    public function fetchAll();
}