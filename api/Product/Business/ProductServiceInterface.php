<?php

namespace Product\Business;

use Shared\AbstractBaseServiceInterface;
use Product\Data\ProductEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<ProductEntity>
 */
interface ProductServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param string $name
     * @param string $description
     * @param array $image
     * @param float $price
     * @param int $category_id
     * @param int $user_id
     * @return ProductEntity
     */
    public function create(
        string $name,
        string $description,
        array $image,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug
    );

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param array $image
     * @param float $price
     * @param float $old_price
     * @param int $stock_qty
     * @param int $category_id
     * @param int $user_id
     * @param string $slug
     * @return ProductEntity
     */
    public function update(
        int $id,
        string $name,
        string $description,
        array $image,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug
    );

    public function remove(int $id);

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForAdmin(array $filters = []);

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForFrontend(array $filters = []);

    /**
     * @param int $merchant_id
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForMerchant(int $merchant_id,array $filters = []);

    /**
     * @param int $id
     * @return ProductEntity
     */
    public function findById(int $id);

    /**
     * @param string $slug
     * @return ProductEntity
     */
    public function findBySlug(string $slug);

    /**
     * @param string $uuid
     * @return ProductEntity
     */
    public function findByUuid(string $uuid);
}
