<?php 

namespace Data\User;

use Data\AbstractBaseRepository;
use Data\User\UserEntity;
use Data\User\UserRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

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

}