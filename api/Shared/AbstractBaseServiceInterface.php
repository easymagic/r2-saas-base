<?php 
namespace Shared;

/**
 * Abstract base service interface
 * @template T of object
 */
interface AbstractBaseServiceInterface
{
    /**
     * Find a record by id
     * @param int $id
     * @return T
     */
    public function find(int $id);
    /**
     * Find a record by a field
     * @param string $field
     * @param string $value
     * @return T
     */
    public function findBy(string $field, string $value);
    /**
     * Delete a record by id
     * @param int $id
     * @return bool
     */
    public function delete(int $id);
    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function filterBy(string $field, string $value);

    /**
     * Filter the data
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters);

    /**
     * Fetch the data
     * @return T[]
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