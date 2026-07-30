<?php 
namespace Infrastructure\Wallet;

use Application\Wallet\WalletMigrationServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class WalletMigrationService implements WalletMigrationServiceInterface {

   private Migration $migration;

    public function __construct(Migration $migration) {
        $this->migration = $migration;
    }

    public function migrate() {
        $this->migration->withTable('wallets')
            ->field('user_id')->definition('INT NOT NULL')->run()
            ->field('reference')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('type')->definition('ENUM(\'manual\',\'online\') DEFAULT \'manual\'')->run() // manual or online
            ->field('amount')->definition('FLOAT DEFAULT 0')->run()
            ->field('balance')->definition('FLOAT DEFAULT 0')->run()
            ->field('description')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('payment_url')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('proof_of_payment_screenshot1')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('proof_of_payment_screenshot2')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('proof_of_payment_screenshot3')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('reason')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('action_by')->definition('INT DEFAULT 0')->run()
            ->field('action_at')->definition('VARCHAR(255) DEFAULT \'\'')->run()
            ->field('status')->definition('ENUM(\'pending\',\'approved\',\'rejected\') DEFAULT \'pending\'')->run()
            ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run();
   
        return "ok";        
    }

}