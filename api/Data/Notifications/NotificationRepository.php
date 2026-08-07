<?php

namespace Data\Notifications;

use Shared\AbstractBaseRepository;
use Data\Notifications\NotificationEntity;
use Data\Notifications\NotificationRepositoryInterface;

use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

class NotificationRepository extends AbstractBaseRepository implements NotificationRepositoryInterface
{
    protected string $table = 'notifications';
    protected string $sql = "SELECT * FROM notifications WHERE 1=1 ";
    protected int $size = 11;
    protected string $hydrateClass = NotificationEntity::class;

    public function __construct(DbServiceInterface $dbService)
    {
        parent::__construct($dbService);
    }

}
