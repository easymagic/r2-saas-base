<?php

namespace Wallet\Data;

use Shared\AbstractBaseRepository;
use Wallet\Data\WalletEntity;
use Wallet\Data\WalletRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use Shared\Query\QueryObject;

/**
 * Wallet Repository
 * @extends AbstractBaseRepository<WalletEntity>
 */
class WalletRepository extends AbstractBaseRepository implements WalletRepositoryInterface
{

    protected string $table = 'wallets';
    protected string $sql = "SELECT * FROM wallets WHERE 1=1 ";
    protected int $size = 10;
    protected string $hydrateClass = WalletEntity::class;

    public function __construct(DbServiceInterface $db)
    {
        parent::__construct($db);

        $this->addFilter("user_id", function (string $value, string &$sql, array &$params) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $value;
        });
        $this->addFilter("status", function (string $value, string &$sql, array &$params) {
            $sql .= " AND status = :status";
            $params['status'] = $value;
        });
        $this->addFilter("type", function (string $value, string &$sql, array &$params) {
            $sql .= " AND type = :type";
            $params['type'] = $value;
        });
        $this->addFilter("amount", function (string $value, string &$sql, array &$params) {
            $sql .= " AND amount = :amount";
            $params['amount'] = $value;
        });
        $this->addFilter("search", function (string $value, string &$sql, array &$params) {
            $sql .= " AND (user_id LIKE :search OR amount LIKE :search OR status LIKE :search OR type LIKE :search)";
            $params['search'] = "%".$value."%";
        });
        $this->addFilter("online", function (bool $value, string &$sql, array &$params) {
            $sql .= " AND type = :type";
            $params['type'] = 'online';
        });
        $this->addFilter("manual", function (bool $value, string &$sql, array &$params) {
            $sql .= " AND type = :type";
            $params['type'] = 'manual';
        });

    }

    public function query(array $filters = [])
    {
        $this->sql = "SELECT * FROM wallets WHERE 1=1 ";
        $this->params = [];
        $this->filter($filters);
        return new QueryObject($this->sql, $this->params, $this->db, $this->hydrateClass);
    }

}
