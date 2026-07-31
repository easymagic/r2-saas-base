<?php 
namespace Infrastructure\Notifications;



class NotificationsMigration
{
    public function migrate()
    {
        $this->createTable('notifications', function ($table) {
            $table->id();
        });
    }
}