<?php

namespace Presentation\Http\Controllers;

use Application\Wallet\WalletServiceInterface;
use Domain\Wallet\WalletRepositoryInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;


class WalletController
{
    private WalletServiceInterface $walletService;
    private Request $request;
    private JsonResponseServiceInterface $response;
    private ApiCredentialServiceInterface $apiCredentialService;
    private WalletRepositoryInterface $walletRepository;

    public function __construct(
        WalletServiceInterface $walletService,
        Request $request,
        JsonResponseServiceInterface $response,
        ApiCredentialServiceInterface $apiCredentialService,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->walletService = $walletService;
        $this->request = $request;
        $this->response = $response;
        $this->apiCredentialService = $apiCredentialService;
        $this->walletRepository = $walletRepository;
    }

    public function topUpOnline()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $amount = $this->request->get('amount');
        $reference = uniqid("TOPUP-") . '-' . time();
        $type = "online";
        $description = "Top up online";
        $status = "pending";
        $payment_url = $this->request->get('payment_url');

        $wallet = $this->walletService->topUpOnline(
            $user_id,
            $amount,
            $reference,
            $type,
            $description,
            $status,
            $payment_url
        );

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Top up online successful'
        ]);
    }

    public function topUpManual()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $amount = $this->request->get('amount');
        $reference = uniqid("TOPUP-") . '-' . time();
        $type = "manual";
        $description = "Top up manual";
        $status = "pending";
        $proof_of_payment_screenshot1 = $this->request->get('proof_of_payment_screenshot1');
        $proof_of_payment_screenshot2 = $this->request->get('proof_of_payment_screenshot2');
        $proof_of_payment_screenshot3 = $this->request->get('proof_of_payment_screenshot3');

        $wallet = $this->walletService->topUpManual(
            $user_id,
            $amount,
            $reference,
            $type,
            $description,
            $status,
            $proof_of_payment_screenshot1,
            $proof_of_payment_screenshot2,
            $proof_of_payment_screenshot3
        );

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Top up manual successful'
        ]);
    }

    public function approveManualTopUp()
    {
        $wallet_id = $this->request->get('wallet_id');
        $status = 'approved';

        $wallet = $this->walletService->approveManualTopUp($wallet_id, $status);

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Manual top up approved successfully'
        ]);
    }

    public function rejectManualTopUp()
    {
        $wallet_id = $this->request->get('wallet_id');
        $status = 'rejected';
        $reason = $this->request->get('reason');

        $wallet = $this->walletService->rejectManualTopUp($wallet_id, $status, $reason);

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Manual top up rejected successfully'
        ]);
    }

    public function myPendingWalletTransactions()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletRepository->pendingForUser($user_id)->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Pending wallet transactions fetched successfully'
        ]);
    }

    public function myApprovedWalletTransactions()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletRepository->approvedForUser($user_id)->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Approved wallet transactions fetched successfully'
        ]);
    }

    public function manualPendingWalletTransactions()
    {
        $wallets = $this->walletRepository->manualPending()->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual pending wallet transactions fetched successfully'
        ]);
    }

    public function manualApprovedWalletTransactions()
    {
        $wallets = $this->walletRepository->manualApproved()->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual approved wallet transactions fetched successfully'
        ]);
    }

    public function manualRejectedWalletTransactions()
    {
        $wallets = $this->walletRepository->manualRejected()->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual rejected wallet transactions fetched successfully'
        ]);
    }

    public function migrate()
    {
        $migration = $this->walletService->migrate();
        return $this->response->success([
            'migration' => $migration,
            'message' => 'Wallet migration successful'
        ]);
    }
}
