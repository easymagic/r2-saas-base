<?php

namespace EcomOrder\Data;

use EcomOrder\Data\EcomOrderMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class EcomOrderMigrationRepository implements EcomOrderMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('ecom_orders')
            ->field('user_id')->definition('INT DEFAULT NULL')->run()
            ->field('type')->definition("ENUM('card','wallet','bnpl') NOT NULL")->run()
            ->field('number_of_installment')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('shipping_fee')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('service_charge')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('total_amount')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('is_guest')->definition('TINYINT(1) NOT NULL DEFAULT 0')->run()
            ->field('customer_name')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('customer_address')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('customer_email')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('reference')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('payment_status')->definition("ENUM('pending','paid','failed','part-paid') NOT NULL DEFAULT 'pending'")->run()
            ->field('delivery_status')->definition("ENUM('pending','picked-up','on-the-way','delivered') NOT NULL DEFAULT 'pending'")->run()
            ->field('agent_id')->definition('INT DEFAULT NULL')->run()
            ->field('payment_url')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
