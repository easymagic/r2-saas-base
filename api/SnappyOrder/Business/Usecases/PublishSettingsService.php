<?php
namespace SnappyOrder\Business\Usecases;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\Usecases\SetService;

class PublishSettingsService
{
    private SetService $setService;
    private SnappyOrderPricingSupport $snappyOrderPricingSupport;

    public function __construct(
        SetService $setService,
        SnappyOrderPricingSupport $snappyOrderPricingSupport
    ) {
        $this->setService = $setService;
        $this->snappyOrderPricingSupport = $snappyOrderPricingSupport;
    }

    public function execute()
    {
        $this->setService->execute(new SetDto('SERVICE_CHARGE', (string) $this->snappyOrderPricingSupport->getServiceCharge()));
        $this->setService->execute(new SetDto('SHIPPING_COST', (string) $this->snappyOrderPricingSupport->getShippingCost()));
        $this->setService->execute(new SetDto('DOLLAR_TO_NAIRA_RATE', (string) $this->snappyOrderPricingSupport->getDollarToNairaRate()));
    }
}
