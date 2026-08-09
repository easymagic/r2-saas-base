<?php

namespace Log\Data;

use Shared\AbstractBaseRepository;
use Shared\Query\QueryObject;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

/**
 * @extends AbstractBaseRepository<LogEntity>
 */
class LogRepository extends AbstractBaseRepository implements LogRepositoryInterface
{
    protected string $table = 'logs';
    protected string $sql = "SELECT * FROM logs WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = LogEntity::class;

    public function __construct(DbServiceInterface $db)
    {
        parent::__construct($db);

        $this->addFilter("type", function (string $value, string &$sql, array &$params) {
            $sql .= " AND type = :type";
            $params['type'] = $value;
        });

        $this->addFilter("search", function (string $value, string &$sql, array &$params) {
            $sql .= " AND (title LIKE :search OR payload LIKE :search OR response LIKE :search)";
            $params['search'] = "%" . $value . "%";
        });
    }

    /**
     * @param array $filters
     * @return QueryObject<LogEntity>
     */
    public function query(array $filters = [])
    {
        $this->sql = "SELECT * FROM logs WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        $this->sql .= " ORDER BY id DESC";
        return new QueryObject($this->sql, $this->params, $this->db, $this->hydrateClass);
    }
}
