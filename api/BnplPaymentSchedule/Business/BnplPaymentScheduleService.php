<?php

namespace BnplPaymentSchedule\Business;

use App\Shared\Contracts\Contracts;
use BnplPaymentSchedule\Data\BnplPaymentScheduleEntity;
use BnplPaymentSchedule\Data\BnplPaymentScheduleMigrationRepositoryInterface;
use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Business\EcomOrderNotificationServiceInterface;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Exception;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;
use Shared\AbstractBaseService;

/**
 * @extends AbstractBaseService<BnplPaymentScheduleEntity, BnplPaymentScheduleRepositoryInterface>
 */
class BnplPaymentScheduleService extends AbstractBaseService implements BnplPaymentScheduleServiceInterface
{
    private BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepositoryInterface;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private EcomOrderNotificationServiceInterface $ecomOrderNotificationService;
    private PaymentServiceInterface $paymentService;

    public function __construct(
        BnplPaymentScheduleMigrationRepositoryInterface $bnplPaymentScheduleMigrationRepositoryInterface,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        EcomOrderRepositoryInterface $ecomOrderRepository,
        EcomOrderNotificationServiceInterface $ecomOrderNotificationService,
        PaymentServiceInterface $paymentService
    ) {
        parent::__construct($bnplPaymentScheduleRepository);
        $this->bnplPaymentScheduleMigrationRepositoryInterface = $bnplPaymentScheduleMigrationRepositoryInterface;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->ecomOrderNotificationService = $ecomOrderNotificationService;
        $this->paymentService = $paymentService;
    }

    public function migrate()
    {
        return $this->bnplPaymentScheduleMigrationRepositoryInterface->migrate();
    }

    /**
     * return first schedule
     * @param int $order_id
     * @param int $number_of_installment
     * @param float $installment_amount
     * @param string $reference
     * @param string $authorization_code
     * @return BnplPaymentScheduleEntity
     */
    public function createSchedules(
        int $order_id,
        int $number_of_installment,
        float $installment_amount,
        string $reference,
        string $authorization_code
    ) {
        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($number_of_installment > 0, 'Number of installment must be greater than 0');
        Contracts::requires($installment_amount > 0, 'Installment amount must be greater than 0');
        $reference = trim($reference);
        Contracts::requiresNotNullOrEmpty($reference, 'reference');

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $first = null;
        for ($i = 0; $i < $number_of_installment; $i++) {
            $schedule = $this->bnplPaymentScheduleRepository->save(0, [
                'order_id' => $order_id,
                'installment_amount' => $installment_amount,
                'payment_status' => 'pending',
                'reference' => $reference,
                'authorization_code' => $authorization_code,
                'number_of_attempts' => 0,
                'expected_payment_date' => date('Y-m-d', strtotime('+' . $i . ' month')),
            ]);
            if ($first === null) {
                $first = $schedule;
            }
        }

        return $first;
    }

    /**
     * @param int $order_id
     * @return BnplPaymentScheduleEntity
     */
    public function getFirstSchedule(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $schedules = $this->bnplPaymentScheduleRepository->query([
            'order_id' => $order_id,
        ])->fetchAll();
        Contracts::requires(!empty($schedules), 'No BNPL schedules found for this order');
        return $schedules[0];
    }

    /**
     * @param int $order_id
     * @return BnplPaymentScheduleEntity
     */
    public function getNextSchedule(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $schedules = $this->bnplPaymentScheduleRepository->query([
            'order_id' => $order_id,
            'payment_status' => 'pending',
        ])->fetchAll();
        Contracts::requires(!empty($schedules), 'No pending BNPL schedule found for this order');
        return $schedules[0];
    }

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function isSchedulePaid(int $schedule_id)
    {
        $schedule = $this->requireSchedule($schedule_id);
        return $schedule->payment_status === 'paid';
    }

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function isSchedulePending(int $schedule_id)
    {
        $schedule = $this->requireSchedule($schedule_id);
        return $schedule->payment_status === 'pending';
    }

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function chargeSchedule(int $schedule_id)
    {
        $schedule = $this->requireSchedule($schedule_id);
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
            $this->increaseNumberOfAttempts($schedule_id);
            $this->failScheduleIfMaxAttempts($schedule_id);
            return false;
        }

        $status = '';
        if (is_array($result) && isset($result['data']['status'])) {
            $status = (string) $result['data']['status'];
        }

        if ($status !== 'success') {
            $this->increaseNumberOfAttempts($schedule_id);
            $this->failScheduleIfMaxAttempts($schedule_id);
            return false;
        }

        $chargeReference = $schedule->reference;
        if (is_array($result) && !empty($result['data']['reference'])) {
            $chargeReference = (string) $result['data']['reference'];
        }

        $this->bnplPaymentScheduleRepository->save((int) $schedule->id, [
            'payment_status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'reference' => $chargeReference,
        ]);

        $this->refreshOrderPaymentStatus((int) $order->id);
        return true;
    }

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function increaseNumberOfAttempts(int $schedule_id)
    {
        $schedule = $this->requireSchedule($schedule_id);
        $this->bnplPaymentScheduleRepository->save((int) $schedule->id, [
            'number_of_attempts' => (int) $schedule->number_of_attempts + 1,
        ]);
        return true;
    }

    /**
     * @param int $schedule_id
     * @return bool
     */
    public function currentDateIsPaymentDate(int $schedule_id)
    {
        $schedule = $this->requireSchedule($schedule_id);
        return date('Y-m-d', strtotime($schedule->expected_payment_date)) === date('Y-m-d');
    }

    /**
     * @param int $schedule_id
     * @return BnplPaymentScheduleEntity
     */
    private function requireSchedule(int $schedule_id)
    {
        Contracts::requires($schedule_id > 0, 'Schedule ID is required');
        $schedule = $this->bnplPaymentScheduleRepository->find($schedule_id);
        Contracts::requireEntityFound($schedule, 'BNPL schedule');
        return $schedule;
    }

    /**
     * @param int $schedule_id
     * @return void
     */
    private function failScheduleIfMaxAttempts(int $schedule_id)
    {
        $schedule = $this->bnplPaymentScheduleRepository->find($schedule_id);
        if ((int) $schedule->number_of_attempts >= 3) {
            $this->bnplPaymentScheduleRepository->save((int) $schedule->id, [
                'payment_status' => 'failed',
            ]);
        }
    }

    /**
     * @param int $order_id
     * @return void
     */
    private function refreshOrderPaymentStatus(int $order_id)
    {
        $pending = $this->bnplPaymentScheduleRepository->query([
            'order_id' => $order_id,
            'payment_status' => 'pending',
        ])->fetchAll();

        $order = $this->ecomOrderRepository->find($order_id);
        if ($order->isEmpty()) {
            return;
        }

        if (empty($pending)) {
            $this->ecomOrderRepository->save($order_id, [
                'payment_status' => 'paid',
            ]);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToCustomer($order_id);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToMerchant($order_id);
            $this->ecomOrderNotificationService->sendOrderPaidNotificationToPlatform($order_id);
            return;
        }

        $this->ecomOrderRepository->save($order_id, [
            'payment_status' => 'part-paid',
        ]);
    }
}
