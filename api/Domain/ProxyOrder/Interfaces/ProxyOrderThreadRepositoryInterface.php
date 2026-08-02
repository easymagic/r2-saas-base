<?php 
namespace Domain\ProxyOrder\Interfaces;

use Domain\ProxyOrder\ProxyOrderThreadEntity;

interface ProxyOrderThreadRepositoryInterface
{
    function fetch();
    function count();
    /**
     * @param array $filters
     * @return self
     */
    function filter(array $filters);
    /**
     * @param int $id
     * @return ProxyOrderThreadEntity
     */
    function find(int $id);
    function save(int $id, array $data);
    function delete(int $id);
    /**
     * @param int $orderId
     * @return mixed
     */
    function filterByOrder(int $orderId);
}