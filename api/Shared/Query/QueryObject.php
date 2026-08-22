<?php 
namespace Shared\Query;

use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/** 
 * Query Object is a wrapper around the database query and provides a unified interface for querying the database.
 * @template T of object
 **/
class QueryObject
{
    private string $query = "";
    private array $params = [];
    private int $limit = 10;
    private string $classMap = "";

    private DbServiceInterface $dbService;

    function __construct(string $query, array $params, DbServiceInterface $dbService, string $classMap)
    {
        $this->query = $query;
        $this->params = $params;
        $this->dbService = $dbService;
        $this->classMap = $classMap;
    }

    /**
     * Hydrate the row into the object
     * @param array $row
     * @return T
     */
    function hydrate(array $row){
        $cls = $this->classMap;
        return new $cls($row);
    }

    /**
     * Fetch the results from the database
     * @return array<T>
     */
    function fetch()
    {
        $rows = $this->dbService->paginate($this->query, $this->limit, $this->params);
        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Count the results from the database
     * @return int
     */
    function count(){
        return $this->dbService->count($this->query, $this->params);
    }

    /**
     * Sum the results from the database
     * @param string $column
     * @return float
     */
    function sum(string $column){
        return $this->dbService->sum($this->query, $column, $this->params);
    }

    /**
     * Fetch all the results from the database
     * @return array<T>
     */
    function fetchAll(){
        $rows = $this->dbService->fetchAll($this->query, $this->params);
        return array_map([$this, 'hydrate'], $rows);
    }

    /**
     * Fetch one result from the database
     * @return T
     */
    function fetchOne(){
        $row = $this->dbService->fetchOne($this->query, $this->params);
        if (empty($row) || !is_array($row)) {
            return $this->hydrate([]);
        }
        return $this->hydrate($row);
    }
    
}