<?php 

namespace Shared;

use Exception;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * Abstract base repository
 * @template T of object
 */
abstract class AbstractBaseRepository implements AbstractBaseRepositoryInterface
{
    private DbServiceInterface $db;

    protected string $sql = "";
    protected array $params = [];
    protected string $hydrateClass = '';
    protected int $size = 11;
    protected $filters = [];
    protected $appliedFilters = [];

    protected string $table = '';

    /**
     * Constructor
     * @param DbServiceInterface $db
     */
    public function __construct(DbServiceInterface $db)
    {
        $this->db = $db;
        $this->addFilter("id", function($value,string &$sql,array &$params){
            $sql .= " AND id = :id";
            $params["id"] = $value;
        });
    }

    function addFilter(string $key, callable $callback){
        $this->filters[$key] = $callback;
        return $this;
    }

    function addAppliedFilter(callable $callback){
        $this->appliedFilters[] = $callback;
        return $this;
    }

    /**
     * Filter the data
     * @param array $filters
     * @return $this
     */
    public function filter(array $filters){
        foreach($filters as $key => $value){
            if(isset($this->filters[$key])){
                $this->filters[$key]($value,$this->sql,$this->params);
            }
        }
        foreach($this->appliedFilters as $callback){
            $callback($this->sql,$this->params);
        }
        return $this;
    }

    /**
     * Count the number of rows
     * @return int
     */
    function count(){
        $result = $this->db->count($this->sql, $this->params);
        return $result->count;
    }

    /**
     * Sum a column
     * @param string $column
     * @return float
     */
    function sum(string $column){
        $result = $this->db->sum($this->sql, $column, $this->params);
        return $result;
    }

    /**
     * Hydrate a row into an entity
     * @param array $row
     * @return T
     */
    function hydrate(array $row){
        $cls = $this->hydrateClass;
        return new $cls($row);
    }


    /**
     * Find a row by id
     * @param int $id
     * @return T
     */
    function find(int $id){
        $this->sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $this->params = [];
        $this->params['id'] = $id;
        $result = $this->db->fetchOne($this->sql, $this->params);
        $entity = $this->hydrate($result);
        return $entity;
    }

    

    /**
     * Fetch all rows from the database
     * @return T[]
     */
    function fetchAll(){
        $result = $this->db->fetchAll($this->sql, $this->params);
        return array_map([$this, 'hydrate'], $result);
    }

    /**
     * Find a row by a field
     * @param string $field
     * @param string $value
     * @return T
     */
    function findBy(string $field,string $value){
        $this->sql = "SELECT * FROM {$this->table} WHERE {$field} = :val";
        $this->params = [];
        $this->params['val'] = $value;
        // echo ($this->sql." - ".json_encode($this->params));
        $result = $this->db->fetchOne($this->sql, $this->params);
        $entity = $this->hydrate($result);
        // if(method_exists($entity, 'isEmpty') && $entity->isEmpty()){
        //     throw new Exception("Entity: {$this->table} not found");
        // }
        return $entity;
    }

    /**
     * Filter by a field
     * @param string $field
     * @param string $value
     * @return $this
     */
    function filterBy(string $field,string $value, string $operator = "AND", string $comparison = "="){
        $this->sql .= " {$operator} {$field} {$comparison} :{$field}";
        $this->params[$field] = $value;
        // die($this->sql);
        return $this;
    }

    /**
     * Fetch data from the database
     * @return T[]
     */
    function fetch(){
       $result = $this->db->paginate($this->sql, $this->size, $this->params);
       return array_map([$this, 'hydrate'], $result);
    }


    /**
     * Save an entity
     * @param int $id
     * @param array $data
     * @return T
     */
    function save(int $id, array $data){
        if($id == 0){
            $id = $this->db->insert($this->table, $data);
        }else{
            $this->db->update($this->table, $data, ["id" => $id]);
        }
        return $this->find($id);
    }

    /**
     * Delete an entity
     * @param int $id
     * @return bool
     */
    function delete(int $id){
        $this->db->delete($this->table, ["id" => $id]);
        return true;
    }


}