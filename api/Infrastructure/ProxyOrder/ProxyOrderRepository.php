<?php

namespace Infrastructure\ProxyOrder;

use Application\ProxyOrder\ProxyOrderService;
use Domain\ProxyOrder\Interfaces\ProxyOrderRepositoryInterface;
use Domain\ProxyOrder\ProxyOrderEntity;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;

class ProxyOrderRepository implements ProxyOrderRepositoryInterface
{

    private DbServiceInterface $dbService;
    private QueryBuilderServiceInterface $aggregateQuery;
    private QueryBuilderServiceInterface $singleQuery;

    public function __construct(
        DbServiceInterface $dbService,
        QueryBuilderServiceInterface $aggregateQuery,
        QueryBuilderServiceInterface $singleQuery
    ) {
        $this->dbService = $dbService;
        $this->aggregateQuery = $aggregateQuery;
        $this->singleQuery = $singleQuery;
        $this->aggregateQuery->setSql("SELECT * FROM proxy_orders WHERE 1=1 ");
        $this->aggregateQuery->setParams([]);
        $this->aggregateQuery->setSize(11);
    }

    function fetch() {
        $sql = $this->aggregateQuery->getSql();
        $params = $this->aggregateQuery->getParams();
        $size = $this->aggregateQuery->getSize();
        $rows = $this->dbService->paginate($sql, $size, $params);
        return array_map([$this, "hydrate"], $rows);
    }

    function count() {
        $sql = $this->aggregateQuery->getSql();
        $params = $this->aggregateQuery->getParams();
        return $this->dbService->count($sql, $params);
    }

    /**
     * @param array $filters
     * @return self
     */
    function filter(array $filters) {
        if (isset($filters["type"])){
            $this->filterByType($filters["type"]);
        }
        if (isset($filters["status"])){
            $this->filterByStatus($filters["status"]);
        }
        if (isset($filters["agent_id"])){
            $this->filterByAgent($filters["agent_id"]);
        }
        // search 
        if (isset($filters["search"])){
            $this->filterBySearch($filters["search"]);
        }
        // user_id
        if (isset($filters["user_id"])){
            $this->filterByUserId($filters["user_id"]);
        }
        $this->aggregateQuery->appendSql(" ORDER BY created_at DESC ");
        return $this;
    }

    /**
     * @param int $id
     * @return ProxyOrderEntity
     */
    function find(int $id) {
        $this->singleQuery->setSql("SELECT * FROM proxy_orders WHERE id = :id ");
        $this->singleQuery->setParams(["id" => $id]);
        $data = $this->dbService->fetchOne($this->singleQuery->getSql(), $this->singleQuery->getParams());
        $obj = $this->hydrate($data);
        if ($obj->isEmpty()){
            throw new \Exception("Proxy order not found");
        }
        return $obj;
    }

    private function hydrate(array $data) {
        return new ProxyOrderEntity($data);
    }

    function save(int $id, array $data) {
        if ($id == 0){
            $id = $this->dbService->insert("proxy_orders", $data);
        }else{
            $this->dbService->update("proxy_orders", $data, ["id" => $id]);
        }
        return $this->find($id);
    }

    function delete(int $id) {
        $this->dbService->delete("proxy_orders", ["id" => $id]);
        return true;
    }

    /**
     * @param int $userId
     * @return self
     */
    function filterByUserId(int $userId) {
        $this->aggregateQuery->appendSql(" AND user_id = :user_id ");
        $this->aggregateQuery->appendParams(["user_id" => $userId]);
        return $this;
    }

    /**
     * @param int $agentId
     * @return self
     */
    function filterByAgent(int $agentId) {
        $this->aggregateQuery->appendSql(" AND agent_id = :agent_id ");
        $this->aggregateQuery->appendParams(["agent_id" => $agentId]);
        return $this;
    }

    /**
     * @param string $status
     * @return self
     */
    function filterByStatus(string $status) {
        $this->aggregateQuery->appendSql(" AND status = :status ");
        $this->aggregateQuery->appendParams(["status" => $status]);
        return $this;
    }

    /**
     * @param int $batchId
     * @return self
     */
    function filterByBatch(int $batchId) {
        $this->aggregateQuery->appendSql(" AND batch_id = :batch_id ");
        $this->aggregateQuery->appendParams(["batch_id" => $batchId]);
        return $this;
    }

    /**
     * @param string $type
     * @return self
     */
    function filterByType(string $type) {
        $this->aggregateQuery->appendSql(" AND type = :type ");
        $this->aggregateQuery->appendParams(["type" => $type]);
        return $this;
    }

    /**
     * @param string $search
     * @return self
     */
    function filterBySearch(string $search) {
        $this->aggregateQuery->appendSql(" AND (reference LIKE :search OR link LIKE :search OR description LIKE :search) ");
        $this->aggregateQuery->appendParams(["search" => "%" . $search . "%"]);
        return $this;
    }

    /**
     * @return self
     */
    function filterByPaid() {
        $paidStatuses = implode(",", ProxyOrderService::PAID_STATUSES);
        $this->aggregateQuery->appendSql(" AND status IN ($paidStatuses) ");
        return $this;
    }

    /**
     * @return self
     */
    function filterByPending() {
        $this->aggregateQuery->appendSql(" AND status = 'pending' ");
        return $this;
    }

    /**
     * @param string $column
     * @return float
     */
    function sum(string $column) {
        return $this->dbService->sum($this->aggregateQuery->getSql(), $column, $this->aggregateQuery->getParams());
    }


}
