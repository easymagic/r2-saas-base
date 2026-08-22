<?php
namespace SnappyOrder\Business\Usecases;

use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\Usecases\LogService as ProxyOrderChangeLogService;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\ChangePriceDto;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfPriceChangeService;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class ChangePriceService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private SnappyOrderPricingSupport $snappyOrderPricingSupport;
    private ProxyOrderChangeLogService $proxyOrderChangeLogService;
    private NotifyCustomerOfPriceChangeService $notifyCustomerOfPriceChangeService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        SnappyOrderPricingSupport $snappyOrderPricingSupport,
        ProxyOrderChangeLogService $proxyOrderChangeLogService,
        NotifyCustomerOfPriceChangeService $notifyCustomerOfPriceChangeService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->snappyOrderPricingSupport = $snappyOrderPricingSupport;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
        $this->notifyCustomerOfPriceChangeService = $notifyCustomerOfPriceChangeService;
    }

    public function execute(ChangePriceDto $changePriceDto)
    {
        $order = $this->snappyOrderRepository->find($changePriceDto->order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires($order->status === 'pending', 'Price can only be changed when order status is pending');

        $previousPrice = (string) $order->total_amount_usd;
        $order->total_amount_usd = (string) $changePriceDto->price;
        $order->service_charge_usd = (float) $this->snappyOrderPricingSupport->getServiceCharge();
        $order->shipping_cost_usd = (float) $this->snappyOrderPricingSupport->getShippingCost();
        $order->dollar_to_naira_rate = (float) $this->snappyOrderPricingSupport->getDollarToNairaRate();
        $order->grand_total_naira = (string) $this->snappyOrderPricingSupport->getTotalAmountNaira($changePriceDto->price);
        $order->price_adjustment_sent = 1;
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->execute(new ProxyOrderChangeLogDto(
            $order->id,
            'total_amount_usd',
            $previousPrice,
            (string) $changePriceDto->price
        ));

        $this->notifyCustomerOfPriceChangeService->execute($order->id, $changePriceDto->price);

        return $order;
    }
}
