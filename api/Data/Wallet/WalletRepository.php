<?php

namespace Data\Wallet;

use Data\AbstractBaseRepository;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;
use Data\Wallet\WalletEntity;
use Data\Wallet\WalletRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

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

    // private function hydrate(array $row) {
    //     return new WalletEntity($row);
    // }

    // public function fetch() {
    //     $sql = $this->queryBuilder->getSql();
    //     $params = $this->queryBuilder->getParams();
    //     $limit = $this->queryBuilder->getSize();
    //     return $this->db->paginate($sql, $limit, $params);
    // }

    // public function save(int $id, array $data) {
    //     if ($id > 0) {
    //         $this->db->update('wallets', $data, ['id' => $id]);
    //         return $this->find($id);
    //     }else{
    //         $id = $this->db->insert('wallets', $data);
    //         return $this->find($id);
    //     }
    // }

    // public function delete(int $id) {
    //     $this->db->delete('wallets', ['id' => $id]);
    //     return true;
    // }


    // public function find(int $id) {
    //     $this->queryBuilder->setSql("SELECT * FROM `wallets` WHERE `id`=:id");
    //     $this->queryBuilder->setParams(["id"=>$id]);
    //     $row = $this->db->fetchOne($this->queryBuilder->getSql(), $this->queryBuilder->getParams());
    //     $wallet = $this->hydrate($row);
    //     if ($wallet->isEmpty()){
    //        throw new Exception('Wallet not found');
    //     }
    //     return $wallet;
    // }

    // public function filter(array $filters = []) {
    //     if (isset($filters['user_id'])) {
    //         $this->queryBuilder->appendSql(" AND `user_id`=:user_id");
    //         $this->queryBuilder->appendParams(["user_id"=>$filters['user_id']]);
    //     }
    //     if (isset($filters['status'])) {
    //         $this->queryBuilder->appendSql(" AND `status`=:status");
    //         $this->queryBuilder->appendParams(["status"=>$filters['status']]);
    //     }
    //     if (isset($filters['type'])) {
    //         $this->queryBuilder->appendSql(" AND `type`=:type");
    //         $this->queryBuilder->appendParams(["type"=>$filters['type']]);
    //     }
    //     if (isset($filters['amount'])) {
    //         $this->queryBuilder->appendSql(" AND `amount`=:amount");
    //         $this->queryBuilder->appendParams(["amount"=>$filters['amount']]);
    //     }
    // }

    // public function count() {
    //     $sql = $this->queryBuilder->getSql();
    //     $params = $this->queryBuilder->getParams();
    //     return $this->db->count($sql, $params);
    // }

    // public function fetchAll() {
    //     $sql = $this->queryBuilder->getSql();
    //     $params = $this->queryBuilder->getParams();
    //     $rows = $this->db->fetchAll($sql, $params);
    //     return array_map([$this, 'hydrate'], $rows);
    // }


    // /**
    //  * Manual pending for user
    //  * @param int $user_id
    //  * @return self
    //  */
    // public function pendingForUser(int $user_id) {
    //     $this->filter(['user_id' => $user_id, 'status' => 'pending']);
    //     return $this;
    // }

    // /**
    //  * Manual approved for user
    //  * @param int $user_id
    //  * @return self
    //  */
    // public function approvedForUser(int $user_id) {
    //     $this->filter(['user_id' => $user_id, 'status' => 'approved']);
    //     return $this;
    // }


    // /**
    //  * Manual pending
    //  * @return self
    //  */
    // public function manualPending() {
    //     $this->filter(['status' => 'pending', 'type' => 'manual']);
    //     return $this;
    // }

    // /**
    //  * Manual approved
    //  * @return self
    //  */
    // public function manualApproved() {
    //     $this->filter(['status' => 'approved', 'type' => 'manual']);
    //     return $this;
    // }

    // /**
    //  * Manual rejected
    //  * @return self
    //  */
    // public function manualRejected() {
    //     $this->filter(['status' => 'rejected', 'type' => 'manual']);
    //     return $this;
    // }

    // /**
    //  * @return self
    //  */
    // public function forUser(int $user_id) {
    //     $this->filter(['user_id' => $user_id]);
    //     return $this;
    // }

    // public function online() {
    //     $this->filter(['type' => 'online']);
    //     return $this;
    // }

    // public function manual() {
    //     $this->filter(['type' => 'manual']);
    //     return $this;
    // }
}
