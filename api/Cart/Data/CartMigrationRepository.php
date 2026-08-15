<?php

namespace Cart\Data;

use Cart\Data\CartMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class CartMigrationRepository implements CartMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('carts')
            ->field('cart_sess_uuid')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('product_id')->definition('INT NOT NULL')->run()
            ->field('qty')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('price_total')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
