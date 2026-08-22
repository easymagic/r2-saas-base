<?php

namespace Shared;

use Shared\Query\QueryObject;

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
     * Save an entity
     * @param T $data
     * @return T
     */
    function save(object $data);

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


    /**
     * Find an entity by id
     * @param int $id
     * @return T
     */
    function find(int $id);

    /**
     * Query the users
     * @param array $filters
     * @return QueryObject<T>
     */
    public function query(array $filters);
}
