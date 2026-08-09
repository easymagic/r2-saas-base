<?php 
namespace Notification\Data;

use Shared\AbstractBaseRepositoryInterface;
use Shared\Query\QueryObject;

/**
 * Notification Repository Interface
 * @extends AbstractBaseRepositoryInterface<NotificationEntity>
 */
interface NotificationRepositoryInterface extends AbstractBaseRepositoryInterface
{

    /**
     * Get a query object
     * @return QueryObject
     */
    public function query(array $filters);
}