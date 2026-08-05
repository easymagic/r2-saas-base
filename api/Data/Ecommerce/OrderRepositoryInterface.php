<?php

namespace App\Domain\Order;

interface OrderRepositoryInterface
{
    public function fetchAll();
    /**
     * @param array $filters
     * @return OrderRepositoryInterface
     */
    public function filter(array $filters);
    public function count();
    public function sum(string $column);
    public function fetch();


    public function find(int $id);
    public function save(int $id, array $data);
    public function delete(int $id);
}