<?php 
namespace Domain\ProxyOrder\Interfaces;

interface ProxyOrderChangeLogRepositoryInterface
{
    function fetch();
    function count();
    /**
     * @param array $filters
     * @return self
     */
    function filter(array $filters);
    function find(int $id);
    function save(int $id, array $data);
    function delete(int $id);
}