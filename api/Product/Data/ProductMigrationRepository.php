<?php

namespace Product\Data;

use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class ProductMigrationRepository implements ProductMigrationRepositoryInterface
{
    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate()
    {
        $this->migration->withTable('products')
            ->field('name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('description')->definition('TEXT DEFAULT NULL')->run()
            ->field('image')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('price')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('uuid')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('old_price')->definition('FLOAT DEFAULT 0')->run()
            ->field('stock_qty')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('active')->definition('TINYINT(1) NOT NULL DEFAULT 1')->run()
            ->field('slug')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('user_id')->definition('INT NOT NULL')->run()
            ->field('category_id')->definition('INT NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
