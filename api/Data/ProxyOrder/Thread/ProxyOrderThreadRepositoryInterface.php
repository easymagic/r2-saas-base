<?php 
namespace Data\ProxyOrder\Thread;



interface ProxyOrderThreadRepositoryInterface
{
    function fetch();
    function count();
    /**
     * @param int $id
     * @return ProxyOrderThreadEntity
     */
    function find(int $id);
    function save(int $id, array $data);
    function delete(int $id);
    /**
     * @param int $orderId
     * @return self
     */
    function filterByOrder(int $orderId);
}