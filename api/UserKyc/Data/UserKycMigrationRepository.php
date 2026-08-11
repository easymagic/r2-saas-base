<?php

namespace UserKyc\Data;

use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class UserKycMigrationRepository implements UserKycMigrationRepositoryInterface
{
    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate()
    {
        $this->migration->withTable('user_kycs')
            ->field('user_id')->definition('INT NOT NULL')->run()
            ->field('nin')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('store_name')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('description')->definition('TEXT DEFAULT NULL')->run()
            ->field('document1')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('document2')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('document3')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('document4')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('document5')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('approved')->definition('TINYINT(1) NOT NULL DEFAULT 0')->run()
            ->field('approved_by')->definition('INT DEFAULT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
