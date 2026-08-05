<?php 
namespace Business;

use Data\AbstractBaseRepositoryInterface;
use Exception;

abstract class AbstractBaseService implements AbstractBaseServiceInterface
{
    protected AbstractBaseRepositoryInterface $repository;

    public function __construct(AbstractBaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
 
    public function find(int $id){
        $obj = $this->repository->find($id);
        if (method_exists($obj, 'isEmpty') && $obj->isEmpty()){
            throw new Exception('Record not found');
        }
        return $obj;
    }

    public function findBy(string $field, string $value){
        $obj = $this->repository->findBy($field, $value);
        if (method_exists($obj, 'isEmpty') && $obj->isEmpty()){
            throw new Exception('Record not found');
        }
        return $obj;
    }

    public function delete(int $id){
        $this->repository->delete($id);
    }

    public function filterBy(string $field, string $value){
        $this->repository->filterBy($field, $value);
        return $this;
    }

    public function filter(array $filters){
        $this->repository->filter($filters);
        return $this;
    }

    public function fetch(){
        return $this->repository->fetch();
    }

    public function count(){
        return $this->repository->count();
    }

    public function sum(string $column){
        return $this->repository->sum($column);
    }
}