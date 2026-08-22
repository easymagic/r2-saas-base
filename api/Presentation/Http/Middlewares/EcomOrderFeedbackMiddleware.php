<?php

namespace Presentation\Http\Middlewares;

use EcomOrder\Business\Dtos\PaymentFeedbackDto;
use EcomOrder\Business\Usecases\PaymentFeedbackService;
use EcomOrder\Business\Usecases\PendingPaymentsForUserService;
use EcomOrder\Data\EcomOrderEntity;
use Log\Business\Dtos\CreateLogDto;
use Log\Business\LogServiceInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class EcomOrderFeedbackMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private PendingPaymentsForUserService $pendingPaymentsForUserService;
    private PaymentFeedbackService $paymentFeedbackService;
    private LogServiceInterface $logService;
    private Request $request;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        PendingPaymentsForUserService $pendingPaymentsForUserService,
        PaymentFeedbackService $paymentFeedbackService,
        LogServiceInterface $logService,
        Request $request
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->pendingPaymentsForUserService = $pendingPaymentsForUserService;
        $this->paymentFeedbackService = $paymentFeedbackService;
        $this->logService = $logService;
        $this->request = $request;
    }

    public function handle()
    {
        $user = $this->resolveUser();
        if ($user === null) {
            return;
        }

        $orders = $this->pendingPaymentsForUserService->query((int) $user->id);
        /** @var EcomOrderEntity $order */
        foreach ($orders as $order) {
            $this->logService->createLog(new CreateLogDto('ecom_order_feedback', json_encode($order), json_encode([
                'reference' => $order->reference,
            ]), 'info'));

            try {
                $updated = $this->paymentFeedbackService->execute(new PaymentFeedbackDto(
                    (int) $order->id,
                    (string) $order->reference
                ));
                $level = $updated->payment_status === 'failed' ? 'error' : 'success';
                $this->logService->createLog(new CreateLogDto('ecom_order_feedback', json_encode($order), json_encode([
                    'payment_status' => $updated->payment_status,
                ]), $level));
            } catch (\Exception $e) {
                $this->logService->createLog(new CreateLogDto(
                    'ecom_order_feedback',
                    json_encode($order),
                    json_encode(['error' => $e->getMessage()]),
                    'error'
                ));
            }
        }
    }

    private function resolveUser()
    {
        try {
            $user = $this->apiCredentialService->getAuthUser();
            if ($user !== null && !$user->isEmpty()) {
                return $user;
            }
        } catch (\Throwable $e) {
        }

        $xUserToken = (string) $this->request->get('x-user-token', '');
        if ($xUserToken === '') {
            return null;
        }

        $this->apiCredentialService->validateUserToken($xUserToken);
        return $this->apiCredentialService->getAuthUser();
    }
}
