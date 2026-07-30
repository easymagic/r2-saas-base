<?php 

namespace Infrastructure\User;

use App\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Domain\User\Exceptions\UserEmailNotFoundException;
use Domain\User\Exceptions\UserIdNotFoundException;
use Domain\User\UserEntity;
use Domain\User\UserRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

class UserRepository implements UserRepositoryInterface
{

   private DbServiceInterface $db;
   private QueryBuilderServiceInterface $queryBuilder;

   public function __construct(DbServiceInterface $db, QueryBuilderServiceInterface $queryBuilder)
   {
      $this->db = $db;
      $this->queryBuilder = $queryBuilder;
      $this->queryBuilder->setSql("SELECT * FROM users WHERE 1=1 ");
      $this->queryBuilder->setSize(10);
   }

   private function hydrate(array $data){
     $record = new UserEntity($data);
     return $record;
   }

    public function fetchAll(){
        $sql = $this->queryBuilder->getSql();
        $params = $this->queryBuilder->getParams();
        $rows = $this->db->fetchAll($sql, $params);
        return array_map([$this, 'hydrate'], $rows);
    }


    public function filter(array $filters){
      if (isset($filters['search'])){
        $this->queryBuilder->appendSql(" AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR role LIKE :search)");
        $this->queryBuilder->appendParams(['search' => "%".$filters['search']."%"]);
      }
      // email
      if (isset($filters['email'])){
        $this->queryBuilder->appendSql(" AND email = :email");
        $this->queryBuilder->appendParams(['email' => $filters['email']]);
      }
      return $this;
    }

    public function count(){
        $sql = $this->queryBuilder->getSql();
        $params = $this->queryBuilder->getParams();
        return $this->db->count($sql, $params);
    }

    public function fetch(){
        $sql = $this->queryBuilder->getSql();
        $params = $this->queryBuilder->getParams();
        $limit = $this->queryBuilder->getSize();
        return $this->db->paginate($sql, $limit, $params);
    }

    public function find(int $id){
        $this->queryBuilder->setSql("SELECT * FROM users WHERE id = :id");
        $this->queryBuilder->setParams(['id' => $id]);
        $row = $this->db->fetchOne($this->queryBuilder->getSql(), $this->queryBuilder->getParams());
        $user = $this->hydrate($row);
        if ($user->isEmpty()){
           throw new Exception('User not found');
        }
        return $user;
    }

    public function findByEmail(string $email){
        $this->queryBuilder->setSql("SELECT * FROM users WHERE email = :email");
        $this->queryBuilder->setParams(['email' => $email]);
        $row = $this->db->fetchOne($this->queryBuilder->getSql(), $this->queryBuilder->getParams());
        if ($row->isEmpty()){
           throw UserEmailNotFoundException::forEmail($email);
        }
        return $this->hydrate($row);
    }

    public function save(int $id, array $data){
      if ($id > 0){
         $this->db->update("users",$data,['id' => $id]);
         return $this->find($id);
      }else{
         $id =$this->db->insert("users",$data);  
         return $this->find($id);
      }
    }

    public function delete(int $id){
        $this->db->delete("users",['id' => $id]);
        return true;
    }

}