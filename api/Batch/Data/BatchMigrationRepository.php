<?php

namespace Batch\Data;

use Batch\Data\BatchMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class BatchMigrationRepository implements BatchMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('batches')
            ->field('name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('description')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
