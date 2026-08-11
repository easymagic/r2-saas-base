<?php

namespace UserKyc\Data;

use Shared\AbstractBaseRepository;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<UserKycEntity>
 */
class UserKycRepository extends AbstractBaseRepository implements UserKycRepositoryInterface
{
    protected string $table = 'user_kycs';
    protected string $sql = "SELECT * FROM user_kycs WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = UserKycEntity::class;

    public function __construct(DbServiceInterface $db)
    {
        parent::__construct($db);
        $this->addFilter("approved", function(bool $value, string &$sql, array &$params){
            $sql .= " AND approved = :approved";
            $params['approved'] = $value;
        });
        $this->addFilter("approved_by", function(int $value, string &$sql, array &$params){
            $sql .= " AND approved_by = :approved_by";
            $params['approved_by'] = $value;
        });
        // search 
        $this->addFilter("search", function(string $value, string &$sql, array &$params){
            $sql .= " AND (nin LIKE :search OR store_name LIKE :search OR description LIKE :search)";
            $params['search'] = "%$value%";
        });
        $this->addFilter("user_id", function(int $value, string &$sql, array &$params){
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $value;
        });


    }


    public function query(array $filters = [])
    {
        $this->sql = "SELECT * FROM user_kycs WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        return new QueryObject(
            $this->sql,
            $this->params,
            $this->db,
            $this->hydrateClass
        );
    }
}
