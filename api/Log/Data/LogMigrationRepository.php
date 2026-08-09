<?php

namespace Log\Data;

use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class LogMigrationRepository implements LogMigrationRepositoryInterface
{
    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate()
    {
        $this->migration->withTable('logs')
            ->field('title')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('payload')->definition('TEXT DEFAULT NULL')->run()
            ->field('response')->definition('TEXT DEFAULT NULL')->run()
            ->field('type')->definition("ENUM('success','error','info') NOT NULL")->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
