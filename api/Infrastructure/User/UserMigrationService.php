<?php 

namespace Infrastructure\User;

use Application\User\UserMigrationServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class UserMigrationService implements UserMigrationServiceInterface
{

   private Migration $migration;

    public function __construct(Migration $migration){
        $this->migration = $migration;
    }

    public function migrate(){
        $this->migration->withTable('users')
        ->field('name')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('email')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('password')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('phone')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('country_code')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('role')->definition("ENUM('admin','customer','staff','super-admin','agent','staff2') DEFAULT 'customer'")->run()
        ->field('status')->definition("ENUM('active','inactive') DEFAULT 'active'")->run()
        ->field('email_verified_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ->field('otp')->definition('VARCHAR(255) DEFAULT NULL')->run()
        // social security number
        ->field('social_security_number')->definition('VARCHAR(255) DEFAULT NULL')->run()
        // wallet balance
        ->field('wallet_balance')->definition('FLOAT DEFAULT 0.00')->run()
        // token
        ->field('token')->definition('VARCHAR(255) DEFAULT NULL')->run()
        ->field('delivery_address')->definition('VARCHAR(255) DEFAULT NULL')->run();
        // echo '<br />User migration [system] completed';
        // dd(dbErrors());

        return "ok";
    }
}