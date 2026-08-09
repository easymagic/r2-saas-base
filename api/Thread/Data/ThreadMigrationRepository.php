<?php

namespace Thread\Data;

use Thread\Data\ThreadMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class ThreadMigrationRepository implements ThreadMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('threads')
            ->field('order_id')->definition('INT NOT NULL')->run()
            ->field('sender_id')->definition('INT NOT NULL')->run()
            ->field('message')->definition('TEXT NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('attachment_url')->definition('VARCHAR(255) DEFAULT NULL')->run();

        return "ok";
    }
}
