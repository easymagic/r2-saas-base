<?php
namespace OrderItem\Business\Usecases;

use Exception;
use OrderItem\Business\Dtos\FetchForMerchantDto;
use OrderItem\Data\OrderItemRepositoryInterface;

class FetchForMerchantService
{
    private OrderItemRepositoryInterface $orderItemRepository;
    private OrderItemSupport $orderItemSupport;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemSupport $orderItemSupport
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemSupport = $orderItemSupport;
    }

    public function query(FetchForMerchantDto $fetchForMerchantDto)
    {
        $filters = [
            'merchant_id' => $fetchForMerchantDto->merchant_id,
            'settled' => $fetchForMerchantDto->settled,
        ];

        if ($fetchForMerchantDto->product_id > 0) {
            $filters['product_id'] = $fetchForMerchantDto->product_id;
        }

        $date_from = trim($fetchForMerchantDto->date_from);
        $date_to = trim($fetchForMerchantDto->date_to);

        if ($date_from !== '') {
            $filters['date_from'] = $this->orderItemSupport->normalizeDateBound($date_from, false);
        }

        if ($date_to !== '') {
            $filters['date_to'] = $this->orderItemSupport->normalizeDateBound($date_to, true);
        }

        if (isset($filters['date_from'], $filters['date_to']) && $filters['date_from'] > $filters['date_to']) {
            throw new Exception('date_from cannot be after date_to');
        }

        return $this->orderItemRepository->query($filters);
    }
}
