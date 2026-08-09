<?php

namespace SnappyOrder\Business;

use Shared\AbstractBaseServiceInterface;
use SnappyOrder\Data\SnappyOrderEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<SnappyOrderEntity>
 */
interface SnappyOrderServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * Migrate the orders from the old system to the new system
     * @return void
     */
    public function migrate();

    /**
     * @param int $user_id
     * @param string $link
     * @param string $description
     * @param array $screen_shot1
     * @param array $screen_shot2
     * @param array $screen_shot3
     * @param float $total_amount_usd
     * @return SnappyOrderEntity
     */
    public function create(
        int $user_id,
        string $link,
        string $description,
        array $screen_shot1,
        array $screen_shot2,
        array $screen_shot3,
        float $total_amount_usd
    );

    /**
     * @param int $order_id
     * @param string $status Valid statuses are: pending, paid, assigned, completed, cancelled
     * @return SnappyOrderEntity 
     */
    public function changeStatus(int $order_id, string $status);

    /**
     * @param int $order_id
     * @param int $agent_id
     * @return SnappyOrderEntity
     */
    public function assignToAgent(int $order_id, int $agent_id);

    /**
     * @param int $agent_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsAgent(int $agent_id, array $filters = []);

    /**
     * @param int $customer_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsCustomer(int $customer_id, array $filters = []);

    /**
     * @param int $admin_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrderAsAdmin(int $admin_id, array $filters = []);

    /**
     * Publish the settings to the database
     * @return void
     */
    public function publishSettings();

    /**
     * @param int $order_id
     * @param float $price
     * @return SnappyOrderEntity
     */
    public function changePrice(int $order_id, float $price);


    /**
     * @param int $order_id
     * @param int $user_id
     * @return SnappyOrderEntity
     */
    public function payOrderFromWallet(int $order_id, int $user_id);


    /**
     * @param int $order_id
     * @param int $batch_id
     * @return SnappyOrderEntity
     */
    public function assignToBatch(int $order_id, int $batch_id);

}
