<?php

namespace Data\ProxyOrder\Order;

use Shared\AbstractBaseRepository;
use Data\ProxyOrder\Order\ProxyOrderRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;

class ProxyOrderRepository extends AbstractBaseRepository implements ProxyOrderRepositoryInterface
{

    protected string $table = 'proxy_orders';
    protected string $sql = "SELECT * FROM proxy_orders WHERE 1=1 ";
    protected int $size = 11;
    protected string $hydrateClass = ProxyOrderEntity::class;
    
    public function __construct(
        DbServiceInterface $dbService,
    ) {
        parent::__construct($dbService);

        $this->addFilter("type", function (string $value, string &$sql, array &$params) {
            $sql .= " AND type = :type";
            $params["type"] = $value;
        });

        $this->addFilter("status", function (string $value, string &$sql, array &$params) {
            $sql .= " AND status = :status";
            $params["status"] = $value;
        });

        $this->addFilter("agent_id", function (int $value, string &$sql, array &$params) {
            $sql .= " AND agent_id = :agent_id";
            $params["agent_id"] = $value;
        });

        $this->addFilter("user_id", function (int $value, string &$sql, array &$params) {
            $sql .= " AND user_id = :user_id";
            $params["user_id"] = $value;
        });

        $this->addFilter("search", function (string $value, string &$sql, array &$params) {
            $sql .= " AND (reference LIKE :search OR link LIKE :search OR description LIKE :search)";
            $params["search"] = "%" . $value . "%";
        });

        $this->addFilter("paid", function (bool $value, string &$sql, array &$params) {
            $sql .= " AND status IN (" . implode(",", ProxyOrderRepositoryInterface::PAID_STATUSES) . ")";
        });

        $this->addFilter("pending", function (bool $value, string &$sql, array &$params) {
            $sql .= " AND status = 'pending'";
        });

        $this->addFilter("batch_id", function (int $value, string &$sql, array &$params) {
            $sql .= " AND batch_id = :batch_id";
            $params["batch_id"] = $value;
        });

        $this->addAppliedFilter(function (string &$sql, array &$params) {
            $sql .= " ORDER BY created_at DESC ";
        });
    }
}
