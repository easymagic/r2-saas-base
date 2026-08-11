<?php

namespace UserKyc\Business;

use Shared\AbstractBaseServiceInterface;
use UserKyc\Data\UserKycEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<UserKycEntity>
 */
interface UserKycServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function create(
        int $user_id,
        string $nin,
        string $store_name,
        string $description,
        array $document1, // optional
        array $document2, // optional
        array $document3, // optional
        array $document4, // optional
        array $document5 // optional
    );

    public function update(
        int $id,
        int $user_id,
        string $nin,
        string $store_name,
        string $description,
        array $document1, // optional
        array $document2, // optional
        array $document3, // optional
        array $document4, // optional
        array $document5, // optional
    );
    
    public function approve(int $id, int $approved_by);
    public function reject(int $id, int $rejected_by, string $reject_reason);

    
    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForApproval(); // approved = -1
    
    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchApproved(); // approved = 1
    
    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchRejected(); // approved = 0
    
    /**
     * @param int $user_id
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForUser(int $user_id); // user_id = $user_id

    public function isKycCompleted(int $user_id);
}
