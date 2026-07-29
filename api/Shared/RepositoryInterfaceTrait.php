<?php 
namespace Shared;

trait RepositoryInterfaceTrait {
    abstract public function fetch();
    abstract public function save(int $id, array $data);
    abstract public function delete(int $id);
    abstract public function find(int $id);
    abstract public function filter(array $filters = []);
    abstract public function count();
    abstract public function fetchAll();
}