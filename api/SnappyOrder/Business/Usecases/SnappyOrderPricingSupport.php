<?php
namespace SnappyOrder\Business\Usecases;

use PlatformConfig\Business\Usecases\GetService;

/**
 * Shared pricing helpers for snappy order use cases.
 */
class SnappyOrderPricingSupport
{
    private GetService $getService;

    public function __construct(GetService $getService)
    {
        $this->getService = $getService;
    }

    public function getServiceCharge()
    {
        return $this->getService->query('SERVICE_CHARGE', 100);
    }

    public function getShippingCost()
    {
        return $this->getService->query('SHIPPING_COST', 100);
    }

    public function getDollarToNairaRate()
    {
        return $this->getService->query('DOLLAR_TO_NAIRA_RATE', 10);
    }

    public function getTotalAmountNaira(float $amount = 0)
    {
        return ($amount + $this->getServiceCharge() + $this->getShippingCost()) * $this->getDollarToNairaRate();
    }
}
