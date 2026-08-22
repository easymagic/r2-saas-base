<?php
namespace EcomOrder\Business\Usecases;

use PlatformConfig\Business\Dtos\SetDto;
use PlatformConfig\Business\Usecases\SetService;

class PublishSettingsService
{
    private SetService $setService;
    private EcomOrderSupport $ecomOrderSupport;

    public function __construct(
        SetService $setService,
        EcomOrderSupport $ecomOrderSupport
    ) {
        $this->setService = $setService;
        $this->ecomOrderSupport = $ecomOrderSupport;
    }

    public function execute()
    {
        $this->setService->execute(new SetDto('ECOM_SHIPPING_FEE', (string) $this->ecomOrderSupport->getShippingFee()));
        $this->setService->execute(new SetDto('ECOM_SERVICE_CHARGE', (string) $this->ecomOrderSupport->getServiceCharge()));
        $this->setService->execute(new SetDto('ECOM_PERCENTAGE_TO_PLATFORM', (string) $this->ecomOrderSupport->getPercentageToPlatform()));
    }
}
