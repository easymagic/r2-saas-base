<?php
namespace OrderItem\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;
use OrderItem\Business\Dtos\CreateDto;
use OrderItem\Data\OrderItemEntity;
use OrderItem\Data\OrderItemRepositoryInterface;
use Product\Data\ProductRepositoryInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class CreateService
{
    private OrderItemRepositoryInterface $orderItemRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private ProductRepositoryInterface $productRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(CreateDto $createDto)
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
}
