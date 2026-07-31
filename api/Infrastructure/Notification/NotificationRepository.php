<?php

namespace Infrastructure\Notification;

use Domain\Notifications\NotificationEntity;
use Domain\Notifications\NotificationRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Db\DbServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Db\QueryBuilderServiceInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    private DbServiceInterface $dbService;
    private QueryBuilderServiceInterface $queryBuilderService;

    public function __construct(DbServiceInterface $dbService, QueryBuilderServiceInterface $queryBuilderService)
    {
        $this->dbService = $dbService;
        $this->queryBuilderService = $queryBuilderService;

        $this->queryBuilderService->setSql('SELECT * FROM notifications WHERE 1=1');
        $this->queryBuilderService->setSize(11);
    }

    public function fetch()
    {
        $sql = $this->queryBuilderService->getSql();
        $size = $this->queryBuilderService->getSize();
        $params = $this->queryBuilderService->getParams();
        $data = $this->dbService->paginate($sql, $size, $params);
        return array_map([$this, 'hydrate'], $data);
    }

    public function save(int $id, array $data)
    {
        if (empty($id)) {
            $id = $this->dbService->insert('notifications', $data);
        } else {
            $this->dbService->update('notifications', $data, ['id' => $id]);
        }
        return $this->find($id);
    }

    private function hydrate(array $data)
    {
        return new NotificationEntity($data);
    }

    /**
     * @param int $id
     * @return NotificationEntity
     */
    public function find(int $id)
    {
        $this->queryBuilderService->setSql('SELECT * FROM notifications WHERE id = :id');
        $this->queryBuilderService->setParams(['id' => $id]);
        $data = $this->dbService->fetchOne(
            $this->queryBuilderService->getSql(),
            $this->queryBuilderService->getParams()
        );
        $data = $this->hydrate($data);
        if ($data->isEmpty()){
           throw new Exception('Notification not found');
        }
        return $data;
    }

    public function delete(int $id) {
        $this->queryBuilderService->setSql('DELETE FROM notifications WHERE id = :id');
        $this->queryBuilderService->setParams(['id' => $id]);
        $this->dbService->execute($this->queryBuilderService->getSql(), $this->queryBuilderService->getParams());
        return true;
    }

    public function count() {
        $sql = $this->queryBuilderService->getSql();
        $params = $this->queryBuilderService->getParams();
        return $this->dbService->count($sql, $params);
    }

    /**
     * @param int $userId
     * @return self
     */
    public function filterByUserId(int $userId) {
        if (empty($userId)) {
            throw new \Exception('User ID is required');
        }
        $this->queryBuilderService->appendSql(' AND user_id = :userId');
        $this->queryBuilderService->appendParams(['userId' => $userId]);
        return $this;
    }
}
