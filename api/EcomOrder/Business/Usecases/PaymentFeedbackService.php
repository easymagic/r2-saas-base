<?php
namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Business\Dtos\PaymentFeedbackDto;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Shared\Contracts;

class PaymentFeedbackService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private PaymentServiceInterface $paymentService;
    private EcomOrderSupport $ecomOrderSupport;
    private UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService;
    private UpdatePaymentStatusAsPartiallyPaidService $updatePaymentStatusAsPartiallyPaidService;
    private UpdatePaymentStatusAsFailedService $updatePaymentStatusAsFailedService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        PaymentServiceInterface $paymentService,
        EcomOrderSupport $ecomOrderSupport,
        UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService,
        UpdatePaymentStatusAsPartiallyPaidService $updatePaymentStatusAsPartiallyPaidService,
        UpdatePaymentStatusAsFailedService $updatePaymentStatusAsFailedService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->paymentService = $paymentService;
        $this->ecomOrderSupport = $ecomOrderSupport;
        $this->updatePaymentStatusAsPaidService = $updatePaymentStatusAsPaidService;
        $this->updatePaymentStatusAsPartiallyPaidService = $updatePaymentStatusAsPartiallyPaidService;
        $this->updatePaymentStatusAsFailedService = $updatePaymentStatusAsFailedService;
    }

    public function execute(PaymentFeedbackDto $paymentFeedbackDto)
    {
        $order_id = $paymentFeedbackDto->order_id;
        $reference = trim($paymentFeedbackDto->reference);

        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requiresNotNullOrEmpty($reference, 'reference');

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires($order->reference === $reference, 'Payment reference does not match this order');

        if ($order->payment_status === 'paid') {
            return $order;
        }
        Contracts::requires(
            in_array($order->payment_status, ['pending', 'part-paid'], true),
            'Order is not awaiting payment'
        );

        $this->paymentService->verify($reference);
        $status = $this->paymentService->getStatus();

        if ($status === 'success') {
            if ($order->type === 'bnpl') {
                $authorizationCode = (string) $this->paymentService->getAuthorizationCode();
                $this->ecomOrderSupport->markFirstBnplSchedulePaid($order, $authorizationCode);
                $pending = $this->bnplPaymentScheduleRepository->query([
                    'order_id' => (int) $order->id,
                    'payment_status' => 'pending',
                ])->fetchAll();
                if (empty($pending)) {
                    return $this->updatePaymentStatusAsPaidService->execute((int) $order->id);
                }
                return $this->updatePaymentStatusAsPartiallyPaidService->execute((int) $order->id);
            }
            return $this->updatePaymentStatusAsPaidService->execute((int) $order->id);
        }

        if ($status === 'abandoned') {
            return $order;
        }

        return $this->updatePaymentStatusAsFailedService->execute((int) $order->id);
    }
}
