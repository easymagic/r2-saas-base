<?php 
namespace Domain\Wallet;

interface WalletRepositoryInterface {

    public function fetch();
    /**
     * Save a wallet
     * @param int $id
     * @param array $data
     * @return WalletEntity
     */
    public function save(int $id, array $data);
    public function delete(int $id);
    /**
     * Find a wallet by id
     * @param int $id
     * @return WalletEntity
     */
    public function find(int $id);
    public function filter(array $filters = []);
    public function count();
    public function fetchAll();

    /**
     * Manual pending for user
     * @param int $user_id
     * @return self
     */
    public function pendingForUser(int $user_id);

    /**
     * Manual approved for user
     * @param int $user_id
     * @return self
     */
    public function approvedForUser(int $user_id);

    
    /**
     * Manual pending
     * @return self
     */
    public function manualPending();

    /**
     * Manual approved
     * @return self
     */
    public function manualApproved();

    /**
     * Manual rejected
     * @return self
     */
    public function manualRejected();

    /**
     * Manual rejected
     * @return self
     */
    public function forUser(int $user_id);

    /**
     * Online
     * @return self
     */
    public function online();

    /**
     * Manual
     * @return self
     */
    public function manual();

    

}