<?php

namespace Data\ProxyOrder\Order;

use Data\ProxyOrder\Order\ProxyOrderMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class ProxyOrderMigrationRepository implements ProxyOrderMigrationRepositoryInterface
{

    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    /**
     * @return void
     */
    function migrate()
    {

        // Batch Entity
        $this->migration->withTable('batches')
            ->field('name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('description')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        // ProxyOrder Change Log Entity
        $this->migration->withTable('proxy_order_change_log')
            ->field('proxy_order_id')->definition('INT NOT NULL')->run()
            ->field('field_name')->definition('VARCHAR(255) NOT NULL')->run()
            ->field('old_value')->definition('TEXT NOT NULL')->run()
            ->field('new_value')->definition('TEXT NOT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();


        // ProxyOrder Entity
        $this->migration->withTable('proxy_orders')
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
            ->field('status')->definition("ENUM('pending','paid','placed','shipped-to-facility','arrived-at-facility','shipped-to-destination-country','arrived-at-destination-country','arrived-at-destination-facility','ready-for-pickup','delivered','cancelled') NOT NULL DEFAULT 'pending'")->run()
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


        // ProxyOrder Thread Entity
        $this->migration->withTable('proxy_order_threads')
            ->field('proxy_order_id')->definition('INT NOT NULL')->run()
            ->field('sender_id')->definition('INT NOT NULL')->run()
            ->field('message')->definition('TEXT NOT NULL')->run()
            ->field('attachment_url')->definition('VARCHAR(255)')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();
    }
}
