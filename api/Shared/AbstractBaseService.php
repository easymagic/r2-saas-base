<?php 
namespace Shared;

use Shared\AbstractBaseRepositoryInterface;
use Exception;

/**
 * Abstract base service
 * @template T of object
 * @template Y of AbstractBaseRepositoryInterface<T>
 */
abstract class AbstractBaseService implements AbstractBaseServiceInterface
{
    /**
     * The repository
     * @var Y
     */
    protected AbstractBaseRepositoryInterface $repository;

    /**
     * Constructor
     * @param Y $repository
     */
    public function __construct(AbstractBaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
 

    /**
     * Find a record by id
     * @param int $id
     * @return T
     */
    public function find(int $id){
        $obj = $this->repository->find($id);
        if (method_exists($obj, 'isEmpty') && $obj->isEmpty()){
            throw new Exception('Record not found');
        }
        return $obj;
    }

    /**
     * Find a record by a field
     * @param string $field
     * @param string $value
     * @return T
     */
    public function findBy(string $field, string $value){
        $obj = $this->repository->findBy($field, $value);
        if (method_exists($obj, 'isEmpty') && $obj->isEmpty()){
            throw new Exception('Record not found');
        }
        return $obj;
    }

    /**
     * Delete a record by id
     * @param int $id
     * @return bool
     */
    public function delete(int $id){
        $this->repository->delete($id);
    }

    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function filterBy(string $field, string $value){
        $this->repository->filterBy($field, $value);
        return $this;
    }

    /**
     * Filter the data
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters){
        $this->repository->filter($filters);
        return $this;
    }

    /**
     * Fetch the data
     * @return T[]
     */
    public function fetch(){
        return $this->repository->fetch();
    }

    /**
     * Count the number of rows
     * @return int
     */
    public function count(){
        return $this->repository->count();
    }

    /**
     * Sum a column
     * @param string $column
     * @return float
     */
    public function sum(string $column){
        return $this->repository->sum($column);
    }
}