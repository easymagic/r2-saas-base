<?php

namespace Batch\Business;

use Shared\AbstractBaseServiceInterface;
use Batch\Data\BatchEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<BatchEntity>
 */
interface BatchServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();
    public function create(string $name, string $description);
    
    /**
     * Get the batch list
     * @param array $filters
     * @return QueryObject
     */
    public function getBatchList(array $filters = []);

    /**
     * Remove a batch
     * @param int $id
     * @return bool
     */
    public function remove(int $id);
    
}
