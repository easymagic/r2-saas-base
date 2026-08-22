<?php

namespace Product\Business;

use Shared\AbstractBaseServiceInterface;
use Product\Business\Dtos\CreateDto;
use Product\Business\Dtos\DeductStockQtyDto;
use Product\Business\Dtos\UpdateDto;
use Product\Data\ProductEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<ProductEntity>
 */
interface ProductServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function create(CreateDto $createDto);

    public function update(UpdateDto $updateDto);

    public function remove(int $id);

    /**
     * @param array $filters
     * @return QueryObject<ProductEntity>
     */
    public function fetchForAdmin(array $filters = []);

    public function deductStockQty(DeductStockQtyDto $deductStockQtyDto);

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
    public function fetchForMerchant(int $merchant_id, array $filters = []);

    public function findById(int $id);

    public function findBySlug(string $slug);

    public function findByUuid(string $uuid);
}
