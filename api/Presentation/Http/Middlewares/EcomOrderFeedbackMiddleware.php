<?php

namespace Presentation\Http\Middlewares;

use EcomOrder\Business\EcomOrderServiceInterface;
use EcomOrder\Data\EcomOrderEntity;
use Log\Business\LogServiceInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;

class EcomOrderFeedbackMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private EcomOrderServiceInterface $ecomOrderService;
    private LogServiceInterface $logService;
    private Request $request;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        EcomOrderServiceInterface $ecomOrderService,
        LogServiceInterface $logService,
        Request $request
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->ecomOrderService = $ecomOrderService;
        $this->logService = $logService;
        $this->request = $request;
    }

    public function handle()
    {
        $user = $this->resolveUser();
        if ($user === null) {
            return;
        }

        $orders = $this->ecomOrderService->pendingPaymentsForUser((int) $user->id);
        /** @var EcomOrderEntity $order */
        foreach ($orders as $order) {
            $this->logService->createLog('ecom_order_feedback', json_encode($order), json_encode([
                'reference' => $order->reference,
            ]), 'info');

            try {
                $updated = $this->ecomOrderService->paymentFeedback(
                    (int) $order->id,
                    (string) $order->reference
                );
                $level = $updated->payment_status === 'failed' ? 'error' : 'success';
                $this->logService->createLog('ecom_order_feedback', json_encode($order), json_encode([
                    'payment_status' => $updated->payment_status,
                ]), $level);
            } catch (\Exception $e) {
                $this->logService->createLog(
                    'ecom_order_feedback',
                    json_encode($order),
                    json_encode(['error' => $e->getMessage()]),
                    'error'
                );
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
