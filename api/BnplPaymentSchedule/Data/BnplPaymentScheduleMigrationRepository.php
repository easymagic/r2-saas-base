<?php

namespace BnplPaymentSchedule\Data;

use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class BnplPaymentScheduleMigrationRepository implements BnplPaymentScheduleMigrationRepositoryInterface
{
   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('bnpl_payment_schedules')
            ->field('order_id')->definition('INT NOT NULL')->run()
            ->field('installment_amount')->definition('FLOAT NOT NULL DEFAULT 0')->run()
            ->field('payment_status')->definition("ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending'")->run()
            ->field('reference')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('authorization_code')->definition('VARCHAR(255) DEFAULT NULL')->run()
            ->field('number_of_attempts')->definition('INT NOT NULL DEFAULT 0')->run()
            ->field('expected_payment_date')->definition('DATE NOT NULL')->run()
            ->field('paid_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
            ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();

        return "ok";
    }
}
