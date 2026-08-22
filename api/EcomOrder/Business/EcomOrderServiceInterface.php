<?php

namespace EcomOrder\Business;

use Shared\AbstractBaseServiceInterface;
use EcomOrder\Business\Dtos\AssignToAgentDto;
use EcomOrder\Business\Dtos\CheckoutDto;
use EcomOrder\Business\Dtos\PaymentFeedbackDto;
use EcomOrder\Business\Dtos\UpdateDeliveryStatusDto;
use EcomOrder\Data\EcomOrderEntity;
use Shared\Query\QueryObject;

/**
 * @extends AbstractBaseServiceInterface<EcomOrderEntity>
 */
interface EcomOrderServiceInterface extends AbstractBaseServiceInterface
{
    public function migrate();

    /**
     * @param int $user_id
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForUser(int $user_id, array $filters = []);

    /**
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForAdmin(array $filters = []): QueryObject;

    /**
     * @param int $agent_id
     * @param array $filters
     * @return QueryObject
     */
    public function fetchForAgent(int $agent_id, array $filters = []);

    public function checkout(CheckoutDto $checkoutDto);

    public function updateDeliveryStatus(UpdateDeliveryStatusDto $updateDeliveryStatusDto);

    public function updatePaymentStatusAsPaid(int $order_id);

    public function updatePaymentStatusAsPartiallyPaid(int $order_id);

    public function updatePaymentStatusAsFailed(int $order_id);

    public function assignToAgent(AssignToAgentDto $assignToAgentDto);

    public function paymentFeedback(PaymentFeedbackDto $paymentFeedbackDto);

    /**
     * @param int $user_id
     * @return EcomOrderEntity[]
     */
    public function pendingPaymentsForUser(int $user_id);

    public function getPendingPayments();

    public function publishSettings();
}
