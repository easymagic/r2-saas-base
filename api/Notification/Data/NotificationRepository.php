<?php

namespace Notification\Data;

use Shared\AbstractBaseRepository;
use Notification\Data\NotificationEntity;
use Notification\Data\NotificationRepositoryInterface;

use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * Notification Repository
 * @extends AbstractBaseRepository<NotificationEntity>
 */
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
