<?php

namespace OrderItem\Business;

use App\Shared\Contracts\Contracts;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use Product\Data\ProductRepositoryInterface;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
use OrderItem\Business\Dtos\CreateDto;
use OrderItem\Business\Dtos\FetchForMerchantDto;
use OrderItem\Data\OrderItemRepositoryInterface;
use OrderItem\Data\OrderItemEntity;
use OrderItem\Data\OrderItemMigrationRepositoryInterface;
use User\Data\UserRepositoryInterface;

/**
 * @extends AbstractBaseService<OrderItemEntity, OrderItemRepositoryInterface>
 */
class OrderItemService extends AbstractBaseService implements OrderItemServiceInterface
{
    private OrderItemMigrationRepositoryInterface $orderItemMigrationRepositoryInterface;
    private OrderItemRepositoryInterface $orderItemRepository;
    private OrderItemNotificationServiceInterface $orderItemNotificationService;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        OrderItemMigrationRepositoryInterface $orderItemMigrationRepositoryInterface,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderItemNotificationServiceInterface $orderItemNotificationService,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($orderItemRepository);
        $this->orderItemMigrationRepositoryInterface = $orderItemMigrationRepositoryInterface;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemNotificationService = $orderItemNotificationService;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    public function migrate()
    {
        return $this->orderItemMigrationRepositoryInterface->migrate();
    }

    public function create(CreateDto $createDto)
    {
        $order = $this->ecomOrderRepository->find($createDto->order_id);
        Contracts::requireEntityFound($order, 'Order');

        $merchant = $this->userRepository->find($createDto->merchant_id);
        Contracts::requireEntityFound($merchant, 'Merchant');

        $product = $this->productRepository->find($createDto->product_id);
        Contracts::requireEntityFound($product, 'Product');
        Contracts::requires(
            (int) $product->user_id === $createDto->merchant_id,
            'Product does not belong to this merchant'
        );

        return $this->orderItemRepository->save(new OrderItemEntity([
            'order_id' => $createDto->order_id,
            'merchant_id' => $createDto->merchant_id,
            'product_id' => $createDto->product_id,
            'qty' => $createDto->qty,
            'total_line_amount' => $createDto->total_line_amount,
            'settled' => $createDto->settled,
            'percentage_to_platform' => $createDto->percentage_to_platform,
        ]));
    }

    /**
     * @param int $order_item_id
     * @return bool
     */
    public function settle(int $order_item_id)
    {
        if (empty($order_item_id)) {
            throw new Exception('Order item ID is required');
        }

        $orderItem = $this->orderItemRepository->find($order_item_id);
        if ($orderItem->isEmpty()) {
            throw new Exception('Order item not found');
        }

        if ((int) $orderItem->settled === 1) {
            throw new Exception('Order item is already settled');
        }

        $this->orderItemRepository->save($orderItem->id, [
            'settled' => 1,
        ]);

        $this->orderItemNotificationService->notifyMerchantOfSettlement((int) $orderItem->id);
        $this->orderItemNotificationService->notifyPlatformOfSettlement((int) $orderItem->id);

        return true;
    }

    /**
     * @param int $order_id
     * @return QueryObject<OrderItemEntity>
     */
    public function fetchForOrder(int $order_id)
    {
        if (empty($order_id)) {
            throw new Exception('Order ID is required');
        }

        return $this->orderItemRepository->query([
            'order_id' => $order_id,
        ]);
    }

    /**
     * @param int $merchant_id
     * @param int $settled
     * @param int $product_id
     * @param string $date_from
     * @param string $date_to
     * @return QueryObject<OrderItemEntity>
     */
    public function fetchForMerchant(FetchForMerchantDto $fetchForMerchantDto)
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
            $filters['date_from'] = $this->normalizeDateBound($date_from, false);
        }

        if ($date_to !== '') {
            $filters['date_to'] = $this->normalizeDateBound($date_to, true);
        }

        if (isset($filters['date_from'], $filters['date_to']) && $filters['date_from'] > $filters['date_to']) {
            throw new Exception('date_from cannot be after date_to');
        }

        return $this->orderItemRepository->query($filters);
    }

    /**
     * @param string $value
     * @param bool $endOfDay
     * @return string
     */
    private function normalizeDateBound(string $value, bool $endOfDay)
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new Exception($endOfDay ? 'date_to is invalid' : 'date_from is invalid');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d', $timestamp) . ($endOfDay ? ' 23:59:59' : ' 00:00:00');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}
