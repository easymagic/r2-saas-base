<?php
namespace Notification\Business\Usecases;

use Notification\Data\NotificationMigrationRepositoryInterface;

class MigrateService
{
    private NotificationMigrationRepositoryInterface $notificationMigrationRepository;

    public function __construct(
        NotificationMigrationRepositoryInterface $notificationMigrationRepository
    ) {
        $this->notificationMigrationRepository = $notificationMigrationRepository;
    }

    public function execute()
    {
        return $this->notificationMigrationRepository->migrate();
    }
}
