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
    }


    public function query(array $filters = [])
    {
        $this->sql = "SELECT * FROM user_kycs WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        return new QueryObject($this->sql, $this->params,$this->db, $this->hydrateClass);
    }
}
