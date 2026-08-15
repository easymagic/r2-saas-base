<?php

namespace OrderItem\Data;

use OrderItem\Data\OrderItemMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class OrderItemMigrationRepository implements OrderItemMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('order_items')
            ->field('order_id')->definition('INT NOT NULL')->run()
            ->field('merchant_id')->definition('INT NOT NULL')->run()
            ->field('product_id')->definition('INT NOT NULL')->run()
            ->field('qty')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('total_line_amount')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('settled')->definition('TINYINT(1) NOT NULL DEFAULT 0')->run()
            ->field('percentage_to_platform')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
