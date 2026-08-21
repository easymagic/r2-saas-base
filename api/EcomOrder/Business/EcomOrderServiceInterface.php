<?php

namespace EcomOrder\Business;

use Shared\AbstractBaseServiceInterface;
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
    public function fetchForAgent(int $agent_id,array $filters = []);


    /**
     * @param int $user_id
     * @param string $type
     * @param int $number_of_installment
     * @param string $customer_name
     * @param string $customer_address
     * @param string $customer_email
     * @param string $reference
     * @param string $cart_uuid
     * @return EcomOrderEntity
     */
    public function checkout(
        int $user_id,
        string $type,
        int $number_of_installment,
        string $customer_name,
        string $customer_address,
        string $customer_email,
        string $reference,
        string $cart_uuid
    );


    public function updateDeliveryStatus(int $order_id, string $delivery_status);


    public function updatePaymentStatusAsPaid(int $order_id);


    public function updatePaymentStatusAsPartiallyPaid(int $order_id);


    public function updatePaymentStatusAsFailed(int $order_id);


    public function assignToAgent(int $order_id, int $agent_id);

    public function paymentFeedback(int $order_id, string $reference);

    /**
     * Pending card/BNPL orders awaiting Paystack confirmation for a user.
     * @param int $user_id
     * @return EcomOrderEntity[]
     */
    public function pendingPaymentsForUser(int $user_id);

    public function getPendingPayments();

    public function publishSettings();


}
