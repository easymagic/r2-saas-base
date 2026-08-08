<?php

namespace SnappyOrder\Data;

use SnappyOrder\Data\SnappyOrderMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class SnappyOrderMigrationRepository implements SnappyOrderMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('snappy_orders')
            ->field('user_id')->definition('INT NOT NULL')->run()
            ->field('batch_id')->definition('INT DEFAULT NULL')->run()
            ->field('agent_id')->definition('INT DEFAULT NULL')->run()
            ->field('type')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('reference')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('link')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('screen_shot1')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('screen_shot2')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('screen_shot3')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('description')->definition('TEXT DEFAULT NULL')->run()
            ->field('status')->definition("ENUM('pending','paid','assigned','completed','cancelled') NOT NULL DEFAULT 'pending'")->run()
            ->field('total_amount_usd')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('grand_total_naira')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('shipping_cost_usd')->definition('FLOAT DEFAULT NULL')->run()
            ->field('dollar_to_naira_rate')->definition('FLOAT DEFAULT NULL')->run()
            ->field('service_charge_usd')->definition('FLOAT DEFAULT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run()
            ->field('pickup_otp_code')->definition('INT DEFAULT NULL')->run()
            ->field('approve_payment')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('price_adjustment_sent')->definition('INT NOT NULL DEFAULT 0')->run();

        return "ok";
    }
}
