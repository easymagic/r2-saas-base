<?php

namespace ProxyOrderChangeLog\Data;

use ProxyOrderChangeLog\Data\ProxyOrderChangeLogMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class ProxyOrderChangeLogMigrationRepository implements ProxyOrderChangeLogMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('proxy_order_change_log')
            ->field('snappy_order_id')->definition('INT NOT NULL')->run()
            ->field('field_name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('old_value')->definition('TEXT NOT NULL')->run()
            ->field('new_value')->definition('TEXT NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
