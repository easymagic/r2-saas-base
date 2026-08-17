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
     * @param float $shipping_fee
     * @param float $service_charge
     * @param float $total_amount
     * @param int $is_guest
     * @param string $customer_name
     * @param string $customer_address
     * @param string $customer_email
     * @param string $reference
     * @return EcomOrderEntity
     */
    public function checkout(
        int $user_id,
        string $type,
        int $number_of_installment,
        float $shipping_fee,
        float $service_charge,
        float $total_amount,
        int $is_guest,
        string $customer_name,
        string $customer_address,
        string $customer_email,
        string $reference
    );


}
