<?php

namespace OrderItem\Business;

use App\Shared\Contracts\Contracts;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use Product\Data\ProductRepositoryInterface;
use Shared\AbstractBaseService;
use Shared\Query\QueryObject;
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

    public function create(
        int $order_id,
        int $merchant_id,
        int $product_id,
        int $qty,
        float $total_line_amount,
        int $settled,
        float $percentage_to_platform
    ) {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($merchant_id > 0, 'Merchant ID is required');
        Contracts::requires($product_id > 0, 'Product ID is required');
        Contracts::requires($qty > 0, 'Quantity must be greater than 0');
        Contracts::requires($total_line_amount >= 0, 'Total line amount cannot be negative');
        Contracts::requires(in_array($settled, [0, 1], true), 'Settled must be 0 or 1');
        Contracts::requires(
            $percentage_to_platform >= 0 && $percentage_to_platform <= 100,
            'Percentage to platform must be between 0 and 100'
        );

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $merchant = $this->userRepository->find($merchant_id);
        Contracts::requireEntityFound($merchant, 'Merchant');

        $product = $this->productRepository->find($product_id);
        Contracts::requireEntityFound($product, 'Product');
        Contracts::requires(
            (int) $product->user_id === $merchant_id,
            'Product does not belong to this merchant'
        );

        return $this->orderItemRepository->save(0, [
            'order_id' => $order_id,
            'merchant_id' => $merchant_id,
            'product_id' => $product_id,
            'qty' => $qty,
            'total_line_amount' => $total_line_amount,
            'settled' => $settled,
            'percentage_to_platform' => $percentage_to_platform,
        ]);
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
     * @return QueryObject<OrderItemEntity>
     */
    public function fetchForMerchant(int $merchant_id, int $settled = 0, int $product_id = 0)
    {
        if (empty($merchant_id)) {
            throw new Exception('Merchant ID is required');
        }

        $filters = [
            'merchant_id' => $merchant_id,
            'settled' => $settled,
        ];

        if ($product_id > 0) {
            $filters['product_id'] = $product_id;
        }

        return $this->orderItemRepository->query($filters);
    }
}
