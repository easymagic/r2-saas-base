<?php
namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Data\BnplPaymentScheduleRepositoryInterface;
use EcomOrder\Data\EcomOrderEntity;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use PlatformConfig\Business\Usecases\GetService;
use Shared\Contracts;

class EcomOrderSupport
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository;
    private GetService $getService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        BnplPaymentScheduleRepositoryInterface $bnplPaymentScheduleRepository,
        GetService $getService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->bnplPaymentScheduleRepository = $bnplPaymentScheduleRepository;
        $this->getService = $getService;
    }

    public function sanitizeListFilters(array $filters)
    {
        $allowed = [
            'payment_status',
            'delivery_status',
            'type',
            'search',
            'date_from',
            'date_to',
            'reference',
            'user_id',
            'agent_id',
        ];
        $clean = [];
        foreach ($allowed as $key) {
            if (!isset($filters[$key])) {
                continue;
            }
            if ($filters[$key] === '' || $filters[$key] === null) {
                continue;
            }
            $clean[$key] = $filters[$key];
        }
        return $clean;
    }

    public function requirePendingPaymentOrder(int $order_id)
    {
        Contracts::requires($order_id > 0, 'Order ID is required');
        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');
        Contracts::requires(
            in_array($order->payment_status, ['pending', 'part-paid'], true),
            'Order payment status cannot be updated'
        );
        return $order;
    }

    public function markFirstBnplSchedulePaid(EcomOrderEntity $order, string $authorizationCode)
    {
        $schedules = $this->bnplPaymentScheduleRepository->query([
            'order_id' => (int) $order->id,
        ])->fetchAll();
        Contracts::requires(!empty($schedules), 'BNPL schedules not found for this order');

        $first = $schedules[0];
        foreach ($schedules as $schedule) {
            $changed = false;
            if ($authorizationCode !== '') {
                $schedule->authorization_code = $authorizationCode;
                $changed = true;
            }
            if ((int) $schedule->id === (int) $first->id && $schedule->payment_status === 'pending') {
                $schedule->payment_status = 'paid';
                $schedule->paid_at = date('Y-m-d H:i:s');
                $changed = true;
            }
            if ($changed) {
                $this->bnplPaymentScheduleRepository->save($schedule);
            }
        }
    }

    public function getShippingFee()
    {
        return (float) $this->getService->query('ECOM_SHIPPING_FEE', 100);
    }

    public function getServiceCharge()
    {
        return (float) $this->getService->query('ECOM_SERVICE_CHARGE', 100);
    }

    public function getPercentageToPlatform()
    {
        return (float) $this->getService->query('ECOM_PERCENTAGE_TO_PLATFORM', 10);
    }
}
