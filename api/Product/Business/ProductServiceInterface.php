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
     * @param float $price
     * @param float $old_price
     * @param int $stock_qty
     * @param int $category_id
     * @param int $user_id
     * @param string $slug
     * @param array $image_1 Uploaded file (required)
     * @param array $image_2 Uploaded file (optional)
     * @param array $image_3 Uploaded file (optional)
     * @param array $image_4 Uploaded file (optional)
     * @param array $image_5 Uploaded file (optional)
     * @param array $image_6 Uploaded file (optional)
     * @param array $image_7 Uploaded file (optional)
     * @return ProductEntity
     */
    public function create(
        string $name,
        string $description,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug,
        array $image_1,
        array $image_2 = [],
        array $image_3 = [],
        array $image_4 = [],
        array $image_5 = [],
        array $image_6 = [],
        array $image_7 = []
    );

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param float $price
     * @param float $old_price
     * @param int $stock_qty
     * @param int $category_id
     * @param int $user_id
     * @param string $slug
     * @param int $active 0 or 1
     * @param array $image_1 Uploaded file (optional; empty keeps existing)
     * @param array $image_2 Uploaded file (optional)
     * @param array $image_3 Uploaded file (optional)
     * @param array $image_4 Uploaded file (optional)
     * @param array $image_5 Uploaded file (optional)
     * @param array $image_6 Uploaded file (optional)
     * @param array $image_7 Uploaded file (optional)
     * @return ProductEntity
     */
    public function update(
        int $id,
        string $name,
        string $description,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug,
        int $active,
        array $image_1 = [],
        array $image_2 = [],
        array $image_3 = [],
        array $image_4 = [],
        array $image_5 = [],
        array $image_6 = [],
        array $image_7 = [],
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
