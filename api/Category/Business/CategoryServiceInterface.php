<?php

namespace Category\Business;

use Shared\AbstractBaseServiceInterface;
use Category\Business\Dtos\CreateDto;
use Category\Business\Dtos\UpdateDto;
use Category\Data\CategoryEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<CategoryEntity>
 */
interface CategoryServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param CreateDto $createDto
     * @return CategoryEntity
     */
    public function create(CreateDto $createDto);

    /**
     * @param UpdateDto $updateDto
     * @return CategoryEntity
     */
    public function update(UpdateDto $updateDto);

    public function remove(int $id);

    /**
     * @param array $filters
     * @return QueryObject<CategoryEntity>
     */
    public function fetchForAdmin(array $filters = []);

    /**
     * @param array $filters
     * @return QueryObject<CategoryEntity>
     */
    public function fetchForFrontend(array $filters = []);

    public function findById(int $id);

    public function findBySlug(string $slug);
}
