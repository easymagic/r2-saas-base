<?php

namespace Category\Data;

use Shared\AbstractBaseRepository;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<CategoryEntity>
 */
class CategoryRepository extends AbstractBaseRepository implements CategoryRepositoryInterface
{
    protected string $table = 'categories';
    protected string $sql = "SELECT * FROM categories WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = CategoryEntity::class;

    public function __construct(DbServiceInterface $db)
    {
        parent::__construct($db);

        $this->addFilter("search", function (string $value, string &$sql, array &$params) {
            $sql .= " AND (name LIKE :search OR description LIKE :search)";
            $params['search'] = "%" . $value . "%";
        });

        $this->addFilter("parent_id", function ($value, string &$sql, array &$params) {
            $sql .= " AND parent_id = :parent_id";
            $params['parent_id'] = $value;
        });

        $this->addFilter("active", function ($value, string &$sql, array &$params) {
            $sql .= " AND active = :active";
            $params['active'] = $value;
        });

        $this->addFilter("slug", function (string $value, string &$sql, array &$params) {
            $sql .= " AND slug = :slug";
            $params['slug'] = $value;
        });
    }

    public function query(array $filters)
    {
        $this->sql = "SELECT * FROM categories WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        return new QueryObject($this->sql, $this->params,$this->db, $this->hydrateClass);
    }
}
