<?php

namespace OrderItem\Business\Dtos;

use Shared\Contracts;

class FetchForMerchantDto
{
    public int $merchant_id;
    public int $settled;
    public int $product_id;
    public string $date_from;
    public string $date_to;

    public function __construct(
        int $merchant_id,
        int $settled = 0,
        int $product_id = 0,
        string $date_from = '',
        string $date_to = ''
    ) {
        Contracts::requires($merchant_id > 0, 'Merchant ID is required');

        $this->merchant_id = $merchant_id;
        $this->settled = $settled;
        $this->product_id = $product_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }
}
