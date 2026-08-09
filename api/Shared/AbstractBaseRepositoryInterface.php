<?php 

namespace Shared;

/**
 * Abstract base repository interface
 * @template T of object
 */
interface AbstractBaseRepositoryInterface
{

    /**
     * Add a filter
     * @param string $key
     * @param callable $callback
     * @return $this
     */
    function addFilter(string $key, callable $callback);

    /**
     * Add an applied filter
     * @param callable $callback
     * @return $this
     */
    function addAppliedFilter(callable $callback);

    /**
     * Filter the data
     * @param array $filters
     * @return $this
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
     * @return T
     */
    function find(int $id);

    /**
     * Fetch all rows from the database
     * @return T[]
     */
    function fetchAll();

    /**
     * Find a row by a field
     * @param string $field
     * @param string $value
     * @return T
     */
    function findBy(string $field,string $value);

    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @param string $operator
     * @param string $comparison
     * @return $this
     */
    function filterBy(string $field,string $value, string $operator = "AND", string $comparison = "=");

    /**
     * Fetch data from the database
     * @return T[]
     */
    function fetch();


    /**
     * Save an entity
     * @param int $id
     * @param array $data
     * @return T
     */
    function save(int $id, array $data);

    /**
     * Delete an entity
     * @param int $id
     * @return bool
     */
    function delete(int $id);

    /**
     * Hydrate a row into an entity
     * @param array $row
     * @return T
     */
    function hydrate(array $row);

}