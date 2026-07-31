<?php 
namespace Domain\Notifications;

interface NotificationRepositoryInterface
{
    public function fetch();
    public function save(int $id, array $data);
    /**
     * @param int $id
     * @return NotificationEntity
     */
    public function find(int $id);
    public function delete(int $id);
    public function count();
    /**
     * @param int $userId
     * @return self
     */
    public function filterByUserId(int $userId);

}