<?php 

namespace Data;

use Exception;
use R2Packages\Framework\Infrastructure\Framework\Db\DbConnectionServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

interface AbstractBaseRepositoryInterface
{

    /**
     * Add a filter
     * @param string $key
     * @param callable $callback
     * @return self
     */
    function addFilter(string $key, callable $callback);

    /**
     * Add an applied filter
     * @param callable $callback
     * @return self
     */
    function addAppliedFilter(callable $callback);

    /**
     * Filter the data
     * @param array $filters
     * @return self
     */
    public function filter(array $filters);

    /**
     * Count the number of rows
     * @return int
     */
    function count();

    /**
     * Sum a column
     * @param string $column
     * @return float
     */
    function sum(string $column);

    /**
     * Find a row by id
     * @param int $id
     * @return object
     */
    function find(int $id);

    /**
     * Fetch all rows from the database
     * @return array
     */
    function fetchAll();

    /**
     * Find a row by a field
     * @param string $field
     * @param string $value
     * @return object
     */
    function findBy(string $field,string $value);

    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @param string $operator
     * @param string $comparison
     * @return self
     */
    function filterBy(string $field,string $value, string $operator = "AND", string $comparison = "=");

    /**
     * Fetch data from the database
     * @return array
     */
    function fetch();


    /**
     * Save an entity
     * @param int $id
     * @param array $data
     * @return object
     */
    function save(int $id, array $data);

    /**
     * Delete an entity
     * @param int $id
     * @return bool
     */
    function delete(int $id);


}