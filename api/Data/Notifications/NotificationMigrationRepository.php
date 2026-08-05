<?php 
namespace Data\Notifications;

use Data\Notifications\NotificationMigrationRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\Migration;

class NotificationMigrationRepository implements NotificationMigrationRepositoryInterface
{

    private Migration $migration;

    public function __construct(Migration $migration)
    {
        $this->migration = $migration;
    }

    public function migrate()
    {
        $this->migration->withTable('notifications')
        ->field('user_id')->definition('INT NOT NULL')->run()
        ->field('title')->definition('VARCHAR(255) NOT NULL')->run()
        ->field('message')->definition('TEXT NOT NULL')->run()
        ->field('read_at')->definition('TIMESTAMP NULL DEFAULT NULL')->run()
        ->field('is_read')->definition('TINYINT(1) NOT NULL DEFAULT 0')->run()
        ->field('created_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP')->run()
        ->field('updated_at')->definition('TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->run();
   
        return "ok";
    }
}