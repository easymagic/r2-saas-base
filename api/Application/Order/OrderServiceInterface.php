<?php

namespace App\Application\Order;

interface OrderServiceInterface
{
    public function find(int $id);
    public function fetchAll();
    public function filter(array $filters);
    public function count();
    public function fetch();
    public function create(array $data);
    public function changeDeliveryStatus(int $id, string $status);
    public function markAsPaid(int $id);
    public function adjustPrice(int $id, float $price);
    public function delete(int $id);
    public function assignToAgent(int $id, int $agentId);
    public function unassignFromAgent(int $id);
    public function assignToBatch(int $id, int $batchId);
    public function unassignFromBatch(int $id);

}