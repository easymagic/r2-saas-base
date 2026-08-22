<?php

namespace Product\Data;

use Shared\AbstractBaseRepository;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseRepository<ProductEntity>
 */
class ProductRepository extends AbstractBaseRepository implements ProductRepositoryInterface
{
    protected string $table = 'products';
    protected string $sql = "SELECT * FROM products WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = ProductEntity::class;

    public function __construct(DbServiceInterface $db)
    {
        parent::__construct($db);

        $this->addFilter("search", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND (products.name LIKE :search OR products.description LIKE :search OR products.slug LIKE :search OR categories.name LIKE :search OR categories.slug LIKE :search)";
            $params['search'] = "%$value%";
        });

        $this->addFilter("category_id", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.category_id = :category_id";
            $params['category_id'] = $value;
        });

        $this->addFilter("user_id", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.user_id = :user_id";
            $params['user_id'] = $value;
        });

        $this->addFilter("active", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.active = :active";
            $params['active'] = $value;
        });

        $this->addFilter("slug", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.slug = :slug";
            $params['slug'] = $value;
        });

        $this->addFilter("uuid", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.uuid = :uuid";
            $params['uuid'] = $value;
        });

        $this->addFilter("price", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.price = :price";
            $params['price'] = $value;
        });

        $this->addFilter("price_min", function (mixed $value, string &$sql, array &$params) {
            $sql .= " AND products.price >= :price_min";
            $params['price_min'] = $value;
        });

        // sorting (a-z)
        $this->addFilter("sort_a_z", function (mixed $value, string &$sql, array &$params) {
            $sql .= " ORDER BY products.name ASC";
        });

        $this->addFilter("sort_z_a", function (mixed $value, string &$sql, array &$params) {
            $sql .= " ORDER BY products.name DESC";
        });

        $this->addFilter("sort_price_asc", function (mixed $value, string &$sql, array &$params) {
            $sql .= " ORDER BY products.price ASC";
        });

        $this->addFilter("sort_price_desc", function (mixed $value, string &$sql, array &$params) {
            $sql .= " ORDER BY products.price DESC";
        });
    }

    public function query(array $filters = [])
    {
        $this->sql = "SELECT products.* FROM products LEFT JOIN categories ON products.category_id = categories.id WHERE 1=1 ";
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
