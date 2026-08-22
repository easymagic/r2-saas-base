<?php

namespace SnappyOrder\Business;

use Shared\AbstractBaseServiceInterface;
use SnappyOrder\Business\Dtos\AssignToAgentDto;
use SnappyOrder\Business\Dtos\AssignToBatchDto;
use SnappyOrder\Business\Dtos\ChangePriceDto;
use SnappyOrder\Business\Dtos\ChangeStatusDto;
use SnappyOrder\Business\Dtos\CreateDto;
use SnappyOrder\Business\Dtos\PayOrderFromWalletDto;
use SnappyOrder\Data\SnappyOrderEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<SnappyOrderEntity>
 */
interface SnappyOrderServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    public function create(CreateDto $createDto);

    public function changeStatus(ChangeStatusDto $changeStatusDto);

    public function assignToAgent(AssignToAgentDto $assignToAgentDto);

    /**
     * @param int $agent_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsAgent(int $agent_id, array $filters = []);

    /**
     * @param int $customer_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrdersAsCustomer(int $customer_id, array $filters = []);

    /**
     * @param int $admin_id
     * @param array $filters
     * @return QueryObject<SnappyOrderEntity>
     */
    public function getMyOrderAsAdmin(int $admin_id, array $filters = []);

    public function publishSettings();

    public function changePrice(ChangePriceDto $changePriceDto);

    public function payOrderFromWallet(PayOrderFromWalletDto $payOrderFromWalletDto);

    public function assignToBatch(AssignToBatchDto $assignToBatchDto);

    public function unassignFromBatch(int $order_id);

    public function getById(int $id);
}
