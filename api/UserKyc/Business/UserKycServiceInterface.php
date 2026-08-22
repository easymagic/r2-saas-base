<?php

namespace UserKyc\Business;

use Shared\AbstractBaseServiceInterface;
use UserKyc\Business\Dtos\ApproveDto;
use UserKyc\Business\Dtos\CreateDto;
use UserKyc\Business\Dtos\RejectDto;
use UserKyc\Business\Dtos\UpdateDto;
use UserKyc\Data\UserKycEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<UserKycEntity>
 */
interface UserKycServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function create(CreateDto $createDto);

    public function update(UpdateDto $updateDto);

    public function approve(ApproveDto $approveDto);

    public function reject(RejectDto $rejectDto);

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForApproval();

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchApproved();

    /**
     * @return QueryObject<UserKycEntity>
     */
    public function fetchRejected();

    /**
     * @param int $user_id
     * @return QueryObject<UserKycEntity>
     */
    public function fetchForUser(int $user_id);

    public function isKycCompleted(int $user_id);
}
