<?php
namespace SnappyOrder\Business\Usecases;

use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\Usecases\LogService as ProxyOrderChangeLogService;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\ChangeStatusDto;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfPickupOTPService;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfStatusChangeService;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;

class ChangeStatusService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private ProxyOrderChangeLogService $proxyOrderChangeLogService;
    private NotifyCustomerOfPickupOTPService $notifyCustomerOfPickupOTPService;
    private NotifyCustomerOfStatusChangeService $notifyCustomerOfStatusChangeService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        ProxyOrderChangeLogService $proxyOrderChangeLogService,
        NotifyCustomerOfPickupOTPService $notifyCustomerOfPickupOTPService,
        NotifyCustomerOfStatusChangeService $notifyCustomerOfStatusChangeService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
        $this->notifyCustomerOfPickupOTPService = $notifyCustomerOfPickupOTPService;
        $this->notifyCustomerOfStatusChangeService = $notifyCustomerOfStatusChangeService;
    }

    public function execute(ChangeStatusDto $changeStatusDto)
    {
        $order = $this->snappyOrderRepository->find($changeStatusDto->order_id);
        Contracts::requireEntityFound($order, 'order');

        $previousStatus = $order->status;
        $status = $changeStatusDto->status;

        Contracts::requires($status !== 'pending', 'Status cannot be changed back to pending');
        Contracts::requires(
            $status !== 'cancelled' || $previousStatus === 'pending',
            'Can only cancel pending orders'
        );

        if ($status === 'ready-for-pickup') {
            $otp_code = rand(100000, 999999);
            $order->status = $status;
            $order->pickup_otp_code = (int) $otp_code;
            $order = $this->snappyOrderRepository->save($order);
            $this->notifyCustomerOfPickupOTPService->execute($order->id, (string) $otp_code);
        } else {
            if ($status === 'delivered') {
                Contracts::requiresNotNullOrEmpty($changeStatusDto->pickup_otp_code, 'Pickup OTP code');
                Contracts::requires(
                    $changeStatusDto->pickup_otp_code === (string) $order->pickup_otp_code,
                    'Invalid pickup OTP code'
                );
            }
            $order->status = $status;
            $order = $this->snappyOrderRepository->save($order);
        }

        $this->proxyOrderChangeLogService->execute(new ProxyOrderChangeLogDto(
            $order->id,
            'status',
            (string) $previousStatus,
            (string) $status
        ));

        $this->notifyCustomerOfStatusChangeService->execute($order->id, $status);

        return $order;
    }
}
