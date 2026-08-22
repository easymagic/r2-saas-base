<?php

namespace Batch\Business;

use Shared\AbstractBaseServiceInterface;
use Batch\Business\Dtos\CreateDto;
use Batch\Data\BatchEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<BatchEntity>
 */
interface BatchServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param CreateDto $createDto
     * @return BatchEntity
     */
    public function create(CreateDto $createDto);

    /**
     * @param array $filters
     * @return QueryObject
     */
    public function getBatchList(array $filters = []);

    /**
     * @param int $id
     * @return bool
     */
    public function remove(int $id);
}
