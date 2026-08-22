<?php
namespace BnplPaymentSchedule\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Shared\Contracts;

class ChargeScheduleService
{
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private PaymentServiceInterface $paymentService;
    private IncreaseNumberOfAttemptsService $increaseNumberOfAttemptsService;
    private BnplPaymentScheduleSupport $bnplPaymentScheduleSupport;

    public function __construct(
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        PaymentServiceInterface $paymentService,
        IncreaseNumberOfAttemptsService $increaseNumberOfAttemptsService,
        BnplPaymentScheduleSupport $bnplPaymentScheduleSupport
    ) {
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->paymentService = $paymentService;
        $this->increaseNumberOfAttemptsService = $increaseNumberOfAttemptsService;
        $this->bnplPaymentScheduleSupport = $bnplPaymentScheduleSupport;
    }

    public function execute(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleSupport->requireSchedule($schedule_id);
        Contracts::requires($schedule->payment_status === 'pending', 'Schedule is not pending');
        Contracts::requires(
            $schedule->expected_payment_date <= date('Y-m-d'),
            'Schedule is not due for payment'
        );
        $authorizationCode = trim((string) $schedule->authorization_code);
        Contracts::requiresNotNullOrEmpty($authorizationCode, 'authorization code');

        $order = $this->ecomOrderRepository->find((int) $schedule->order_id);
        Contracts::requireEntityFound($order, 'Order');

        try {
            $result = $this->paymentService->authorize(
                $authorizationCode,
                $order->customer_email,
                (float) $schedule->installment_amount
            );
        } catch (Exception $e) {
            $this->increaseNumberOfAttemptsService->execute($schedule_id);
            $this->bnplPaymentScheduleSupport->failScheduleIfMaxAttempts($schedule_id);
            return false;
        }

        $status = '';
        if (is_array($result) && isset($result['data']['status'])) {
            $status = (string) $result['data']['status'];
        }

        if ($status !== 'success') {
            $this->increaseNumberOfAttemptsService->execute($schedule_id);
            $this->bnplPaymentScheduleSupport->failScheduleIfMaxAttempts($schedule_id);
            return false;
        }

        $chargeReference = $schedule->reference;
        if (is_array($result) && !empty($result['data']['reference'])) {
            $chargeReference = (string) $result['data']['reference'];
        }

        $schedule->payment_status = 'paid';
        $schedule->paid_at = date('Y-m-d H:i:s');
        $schedule->reference = $chargeReference;
        $this->bnplPaymentScheduleRepository->save($schedule);

        $this->bnplPaymentScheduleSupport->refreshOrderPaymentStatus((int) $order->id);
        return true;
    }
}
