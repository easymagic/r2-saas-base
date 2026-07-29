<?php 

namespace Infrastructure\User;

use Domain\User\Exceptions\UserEmailNotFoundException;
use Domain\User\Exceptions\UserIdNotFoundException;
use Domain\User\UserEntity;
use Domain\User\UserRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

class UserRepository implements UserRepositoryInterface
{

   private DbServiceInterface $db;

   private $sql = "SELECT * FROM users WHERE 1=1 ";
   private $params = [];
   private $limit = 10;

   public function __construct(DbServiceInterface $db)
   {
      $this->db = $db;
   }

   private function hydrate(array $data){
     return new UserEntity($data);
   }

    public function fetchAll(){
        $rows = $this->db->fetchAll($this->sql, $this->params);
        return array_map([$this, 'hydrate'], $rows);
    }


    public function filter(array $filters){
      if (isset($filters['search'])){
        $this->sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR role LIKE :search)";
        $this->params['search'] = "%".$filters['search']."%";
      }
      // email
      if (isset($filters['email'])){
        $this->sql .= " AND email = :email";
        $this->params['email'] = $filters['email'];
      }
      return $this;
    }

    public function count(){
        return $this->db->count($this->sql, $this->params);
    }

    public function fetch(){
        return $this->db->paginate($this->sql,$this->limit, $this->params);
    }

    public function find(int $id){
        $sql = "SELECT * FROM users WHERE id = :id";
        $params = [
            'id' => $id
        ];
        $row = $this->db->fetchOne($sql, $params);
        if ($row->isEmpty()){
           throw UserIdNotFoundException::forId($id);
        }
        return $this->hydrate($row);
    }

    public function findByEmail(string $email){
        $sql = "SELECT * FROM users WHERE email = :email";
        $params = [
            'email' => $email
        ];
        $row = $this->db->fetchOne($sql, $params);
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