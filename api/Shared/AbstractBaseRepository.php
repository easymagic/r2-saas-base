<?php 

namespace Shared;

use Exception;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * Abstract base repository
 * @template T of object
 */
abstract class AbstractBaseRepository implements AbstractBaseRepositoryInterface
{
    protected DbServiceInterface $db;

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

    /**
     * Add a filter
     * @param string $key
     * @param callable $callback function(mixed $value, string &$sql, array &$params): void
     * @return $this
     */

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
     * Save an entity
     * @param T $data
     * @return T
     */
    function save(object $data){

        $payload = [];

        $vars = get_object_vars($data);
        foreach($vars as $key => $value){
            if($value !== null && !is_array($value) && !is_object($value)){
                $payload[$key] = $value;
            }
        }
        
        if ($data->id == 0){
            $id = $this->db->insert($this->table, $payload);
        }else{
            $this->db->update($this->table, $payload, ["id" => $data->id]);
            $id = $data->id;
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