<?php

namespace Business\ProxyOrder\Order;

use Business\AbstractBaseServiceInterface;
use Data\ProxyOrder\Order\ProxyOrderEntity;

interface ProxyOrderServiceInterface extends AbstractBaseServiceInterface
{
    /**
     * @param int $userId
     * @param string $type
     * @param string $reference
     * @param string $link
     * @param string $description
     * @param float $total_amount_usd
     * @param array $screen_shot1
     * @param mixed $screen_shot2
     * @param mixed $screen_shot3
     * @param string $status
     * @return ProxyOrderEntity
     */
    public function create(
        int $userId,
        string $type,
        string $reference,
        string $link,
        string $description,
        float $total_amount_usd,
        array $screen_shot1,
        mixed $screen_shot2 = [],
        mixed $screen_shot3 = [],
        string $status = 'pending'
    );
    /**
     * @param int $id
     * @return ProxyOrderEntity
     */
    public function find(int $id);

    /**
     * @param int $id
     * @param string $status
     * @return ProxyOrderEntity
     */
    public function updateStatus(int $id, string $status);

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id);

    /**
     * @param int $id
     * @param float $price
     * @return ProxyOrderEntity
     */
    function adjustPrice(int $id, float $price);

    /**
     * @param int $id
     * @param int $batchId
     * @return ProxyOrderEntity
     */
    function assignToBatch(int $id, int $batchId);

    /**
     * @param int $id
     * @param int $agentId
     * @return ProxyOrderEntity
     */
    function assignToAgent(int $id, int $agentId);

    /**
     * Publish the settings to the platform config
     * @return void
     */
    function publishSettings();

    /**
     * Get the dashboard stats
     * @return array
     */
    function dashboardStats();

    /**
     * Get the dashboard stats for a specific user
     * @param int $userId
     * @return array
     */
    function myDashboardStats(int $userId);

    /**
     * Migrate the proxy order data
     * @return void
     */
    function migrate();

    /**
     * Pay from wallet
     * @param int $proxyOrderId
     * @param int $userId
     * @return ProxyOrderEntity
     */
    function payFromWallet(int $proxyOrderId, int $userId);

    /**
     * Approve payment
     * @param int $proxyOrderId
     * @return ProxyOrderEntity
     */
    function approvePayment(int $proxyOrderId);

    /**
     * Cancel payment
     * @param int $proxyOrderId
     * @return ProxyOrderEntity
     */
    function cancelPayment(int $proxyOrderId);
}
