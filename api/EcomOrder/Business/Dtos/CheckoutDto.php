<?php

namespace EcomOrder\Business\Dtos;

use Shared\Contracts;

class CheckoutDto
{
    public int $user_id;
    public string $type;
    public int $number_of_installment;
    public string $customer_name;
    public string $customer_address;
    public string $customer_email;
    public string $reference;
    public string $payment_url = '';
    public string $cart_uuid;
    public int $is_guest = 0;
    public int $order_id = 0;

    public function __construct(
        int $user_id,
        string $type,
        int $number_of_installment,
        string $customer_name,
        string $customer_address,
        string $customer_email,
        string $reference,
        string $cart_uuid
    ) {
        // Guest checkout allowed when user_id is 0
        // Contracts::requires($user_id >= 0, 'User ID is invalid');
        Contracts::requiresNotNullOrEmpty($type, 'Type');
        Contracts::requiresNotNullOrEmpty($customer_name, 'Customer Name');
        Contracts::requiresNotNullOrEmpty($customer_address, 'Customer Address');
        Contracts::requiresNotNullOrEmpty($customer_email, 'Customer Email');
        // Contracts::requiresNotNullOrEmpty($reference, 'Reference');
        if (empty($user_id)){
            $this->is_guest = 1;
        }
        $this->reference = uniqid("ref_");
        Contracts::requiresNotNullOrEmpty($cart_uuid, 'Cart UUID');

        $this->user_id = $user_id;
        $this->type = $type;
        $this->number_of_installment = $number_of_installment;
        $this->customer_name = $customer_name;
        $this->customer_address = $customer_address;
        $this->customer_email = $customer_email;
        // $this->reference = $reference;
        $this->cart_uuid = $cart_uuid;
    }
}
