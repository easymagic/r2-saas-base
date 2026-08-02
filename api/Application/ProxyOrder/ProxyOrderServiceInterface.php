<?php

namespace Application\ProxyOrder;

use Domain\ProxyOrder\ProxyOrderEntity;

interface ProxyOrderServiceInterface
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
    function pusblishSettings();
}
