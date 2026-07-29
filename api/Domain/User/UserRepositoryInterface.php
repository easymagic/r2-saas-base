<?php 

namespace Domain\User;

interface UserRepositoryInterface
{
    public function fetchAll();
    /**
     * @param array $filters
     * @return UserRepositoryInterface
     */
    public function filter(array $filters);
    public function count();
    public function fetch();
    /**
     * @param int $id
     * @return UserEntity
     */
    public function find(int $id);
    /**
     * @param string $email
     * @return UserEntity
     */
    public function findByEmail(string $email);
    /**
     * @param int $id
     * @param array $data
     * @return UserEntity
     */
    public function save(int $id, array $data);

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id);


}