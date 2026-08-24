<?php 

namespace User\Data;

use Shared\AbstractBaseRepository;
use User\Data\UserEntity;

use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<UserEntity>
 */
class UserRepository extends AbstractBaseRepository implements UserRepositoryInterface
{
   protected string $table = 'users';
   protected string $sql = "SELECT * FROM users WHERE 1=1 ";
   protected int $size = 10;
   protected string $hydrateClass = UserEntity::class;

   public function __construct(DbServiceInterface $db)
   {
      parent::__construct($db);
      $this->addFilter("search", function (string $value, string &$sql, array &$params) {
        $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search OR role LIKE :search)";
        $params['search'] = "%".$value."%";
      });
      $this->addFilter("email", function (string $value, string &$sql, array &$params) {
        $sql .= " AND email = :email";
        $params['email'] = $value;
      });
   }

   public function query(array $filters = [])
   {
      $this->sql = "SELECT * FROM users WHERE 1=1 ";
      $this->params = [];
      $this->filter($filters);
      return new QueryObject($this->sql, $this->params, $this->db, $this->hydrateClass);
   }

   public function idExists(int $id)
   {
      $this->sql = "SELECT COUNT(*) FROM users WHERE id = :id";
      $this->params['id'] = $id;
      return $this->db->count($this->sql, $this->params) > 0;
   }

}