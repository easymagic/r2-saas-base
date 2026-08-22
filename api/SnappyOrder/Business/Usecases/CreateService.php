<?php
namespace SnappyOrder\Business\Usecases;

use R2Packages\Framework\Infrastructure\Framework\File\FileUploadServiceInterface;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\CreateDto;
use SnappyOrder\Business\Usecases\Mail\NotifyAdminOfOrderCreationService;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfOrderCreationService;
use SnappyOrder\Data\SnappyOrderEntity;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class CreateService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private UserRepositoryInterface $userRepository;
    private FileUploadServiceInterface $fileUploadService;
    private SnappyOrderPricingSupport $snappyOrderPricingSupport;
    private NotifyCustomerOfOrderCreationService $notifyCustomerOfOrderCreationService;
    private NotifyAdminOfOrderCreationService $notifyAdminOfOrderCreationService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        UserRepositoryInterface $userRepository,
        FileUploadServiceInterface $fileUploadService,
        SnappyOrderPricingSupport $snappyOrderPricingSupport,
        NotifyCustomerOfOrderCreationService $notifyCustomerOfOrderCreationService,
        NotifyAdminOfOrderCreationService $notifyAdminOfOrderCreationService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->snappyOrderPricingSupport = $snappyOrderPricingSupport;
        $this->notifyCustomerOfOrderCreationService = $notifyCustomerOfOrderCreationService;
        $this->notifyAdminOfOrderCreationService = $notifyAdminOfOrderCreationService;
    }

    public function execute(CreateDto $createDto)
    {
        $user = $this->userRepository->find($createDto->user_id);
        Contracts::requireEntityFound($user, 'user');

        $path = '/uploads/snappy_orders';
        $full_path = __DIR__ . '/../../../';

        $screen_shot1_path = $this->fileUploadService->uploadFile($createDto->screen_shot1, $path, $full_path);
        $screen_shot2_path = $this->fileUploadService->uploadFile($createDto->screen_shot2, $path, $full_path);
        $screen_shot3_path = $this->fileUploadService->uploadFile($createDto->screen_shot3, $path, $full_path);

        $order = $this->snappyOrderRepository->save(new SnappyOrderEntity([
            'user_id' => $user->id,
            'type' => 'manual',
            'reference' => uniqid('MANUAL_'),
            'link' => $createDto->link,
            'description' => $createDto->description,
            'total_amount_usd' => (string) $createDto->total_amount_usd,
            'screen_shot1' => $screen_shot1_path ? $screen_shot1_path : '',
            'screen_shot2' => $screen_shot2_path ? $screen_shot2_path : '',
            'screen_shot3' => $screen_shot3_path ? $screen_shot3_path : '',
            'status' => 'pending',
            'service_charge_usd' => (float) $this->snappyOrderPricingSupport->getServiceCharge(),
            'shipping_cost_usd' => (float) $this->snappyOrderPricingSupport->getShippingCost(),
            'dollar_to_naira_rate' => (float) $this->snappyOrderPricingSupport->getDollarToNairaRate(),
            'grand_total_naira' => (string) $this->snappyOrderPricingSupport->getTotalAmountNaira($createDto->total_amount_usd),
            'price_adjustment_sent' => 0,
        ]));

        $this->notifyCustomerOfOrderCreationService->execute($order->id);
        $this->notifyAdminOfOrderCreationService->execute($order->id);

        return $order;
    }
}
