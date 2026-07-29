<?php 
namespace Infrastructure\Wallet;

use Domain\Wallet\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface {

public function fetch(){

}

public function save(int $id, array $data){

}

public function delete(int $id){

}

public function find(int $id){

}

public function filter(array $filters = []){

}

public function count(){

}

public function fetchAll(){

}


/**
 * Manual pending for user
 * @param int $user_id
 * @return self
 */
public function pendingForUser(int $user_id){

}

/**
 * Manual approved for user
 * @param int $user_id
 * @return self
 */
public function approvedForUser(int $user_id){

}


/**
 * Manual pending
 * @return self
 */
public function manualPending(){

}

/**
 * Manual approved
 * @return self
 */
public function manualApproved(){

}

/**
 * Manual rejected
 * @return self
 */
public function forUser(int $user_id){
    
}

}