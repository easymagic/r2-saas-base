<?php 
namespace Data\PlatformConfig;

use Data\PlatformConfig\PlatformConfigMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class PlatformConfigMigrationRepository implements PlatformConfigMigrationRepositoryInterface {

    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate() {
        $this->migration->withTable('platform_configs')
        ->field('setting_key')->definition('VARCHAR(255) NOT NULL')->run()
        ->field('setting_value')->definition('TEXT NOT NULL')->run()
        ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();
        return true;
    }
}