<?php

namespace EcomOrder\Business\Usecases;

use BnplPaymentSchedule\Business\Usecases\UpdateAuthorizarionCodeService;
use EcomOrder\Data\EcomOrderEntity;
use R2Packages\Framework\Infrastructure\Framework\Payment\PaymentServiceInterface;

class CronProcessCardPaymentFeedback
{
    private FetchPendingCardPayments $fetchPendingCardPayments;
    private PaymentServiceInterface $paymentService;
    private UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService;
    private UpdatePaymentStatusAsFailedService $updatePaymentStatusAsFailedService;
    private UpdateAuthorizarionCodeService $updateAuthorizationCodeService;

    public function __construct(
        FetchPendingCardPayments $fetchPendingCardPayments,
        PaymentServiceInterface $paymentService,
        UpdatePaymentStatusAsPaidService $updatePaymentStatusAsPaidService,
        UpdatePaymentStatusAsFailedService $updatePaymentStatusAsFailedService,
        UpdateAuthorizarionCodeService $updateAuthorizationCodeService
    ) 
    {
        $this->fetchPendingCardPayments = $fetchPendingCardPayments;
        $this->paymentService = $paymentService;
        $this->updatePaymentStatusAsPaidService = $updatePaymentStatusAsPaidService;
        $this->updatePaymentStatusAsFailedService = $updatePaymentStatusAsFailedService;
        $this->updateAuthorizationCodeService = $updateAuthorizationCodeService;
    }

    public function execute()
    {
        $pendingCardPayments = $this->fetchPendingCardPayments->query()->fetchAll();
        foreach ($pendingCardPayments as $pendingCardPayment) {
            $this->paymentService->verify($pendingCardPayment->reference);
            
            // abandoned
            $status = $this->paymentService->getStatus();
            
            $failed = ($status !== 'abandoned') && $status !== 'success';
            $this->markAsFailed(
                $failed,
                $pendingCardPayment
            );

            $this->markAsPaid(
                $status === 'success',
                $pendingCardPayment
            );

            $this->updateAuthorizationCode(
                $status === 'success',
                $pendingCardPayment
            );
        }
    }

    private function markAsFailed(bool $condition, EcomOrderEntity $ecomOrderEntity)
    {
        if ($condition) {
            $this->updatePaymentStatusAsFailedService->execute($ecomOrderEntity->id);
        }
    }

    private function markAsPaid(bool $condition, EcomOrderEntity $ecomOrderEntity)
    {
        if ($condition) {
            $this->updatePaymentStatusAsPaidService->execute($ecomOrderEntity->id);
        }
    }

    private function updateAuthorizationCode(bool $condition,EcomOrderEntity $ecomOrderEntity)
    {
        if ($condition) {
            $authorization_code = $this->paymentService->getAuthorizationCode();
            $this->updateAuthorizationCodeService->execute($ecomOrderEntity->id, $authorization_code);
        }

    }
}
