<?php

namespace Category\Data;

use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class CategoryMigrationRepository implements CategoryMigrationRepositoryInterface
{
    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate()
    {
        $this->migration->withTable('categories')
            ->field('name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('parent_id')->definition('INT DEFAULT NULL')->run()
            ->field('image')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('slug')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('description')->definition('TEXT DEFAULT NULL')->run()
            ->field('active')->definition('TINYINT(1) NOT NULL DEFAULT 1')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
