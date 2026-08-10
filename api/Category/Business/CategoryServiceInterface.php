<?php

namespace Category\Business;

use Shared\AbstractBaseServiceInterface;
use Category\Data\CategoryEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<CategoryEntity>
 */
interface CategoryServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param string $name
     * @param int $parent_id
     * @param string $description
     * @param array $image Uploaded file array (same shape as SnappyOrder screenshots)
     * @param string $slug
     * @return CategoryEntity
     */
    public function create(string $name, int $parent_id, string $description, array $image, string $slug);

    /**
     * @param int $id
     * @param string $name
     * @param int $parent_id
     * @param string $description
     * @param array $image Uploaded file array; empty keeps existing image
     * @param string $slug
     * @return CategoryEntity
     */
    public function update(int $id, string $name, int $parent_id, string $description, array $image, string $slug, int $active);

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
