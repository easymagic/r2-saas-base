<?php 
namespace Business;

use Data\AbstractBaseRepositoryInterface;

interface AbstractBaseServiceInterface
{
    /**
     * Find a record by id
     * @param int $id
     * @return object
     */
    public function find(int $id);
    /**
     * Find a record by a field
     * @param string $field
     * @param string $value
     * @return object
     */
    public function findBy(string $field, string $value);
    public function delete(int $id);
    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @return self
     */
    public function filterBy(string $field, string $value);

    /**
     * Filter the data
     * @param array $filters
     * @return self
     */
    public function filter(array $filters);

    /**
     * Fetch the data
     * @return array
     */
    public function fetch();

    /**
     * Count the number of rows
     * @return int
     */
    public function count();

    /**
     * Sum the data
     * @param string $column
     * @return float
     */
    public function sum(string $column);
}