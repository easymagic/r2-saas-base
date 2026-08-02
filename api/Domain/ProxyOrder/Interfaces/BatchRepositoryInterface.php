<?php 
namespace Domain\ProxyOrder\Interfaces;

interface BatchRepositoryInterface
{
    function fetch();
    function count();
    function filter(array $filters);
    function find(int $id);
    function save(int $id, array $data);
    function delete(int $id);
}