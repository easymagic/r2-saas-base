<?php

namespace Wallet\Presentation;

use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Business\WalletServiceInterface;
use Wallet\Data\WalletRepositoryInterface;
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
        $wallet = $this->walletService->topUpOnline(new TopUpOnlineDto(
            (int) $user->id,
            (float) $this->request->get('amount'),
            uniqid('TOPUP-') . '-' . time(),
            'Top up online',
            'pending'
        ));

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Top up online successful'
        ]);
    }

    public function topUpManual()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $wallet = $this->walletService->topUpManual(new TopUpManualDto(
            (int) $user->id,
            (float) $this->request->get('amount'),
            uniqid('TOPUP-') . '-' . time(),
            'Top up manual',
            'pending',
            (array) $this->request->get('proof_of_payment_screenshot1', []),
            $this->request->get('proof_of_payment_screenshot2', []),
            $this->request->get('proof_of_payment_screenshot3', [])
        ));

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Top up manual successful'
        ]);
    }

    public function approveManualTopUp()
    {
        $wallet = $this->walletService->approveManualTopUp(new ApproveManualTopUpDto(
            (int) $this->request->get('wallet_id'),
            'approved'
        ));

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Manual top up approved successfully'
        ]);
    }

    public function rejectManualTopUp()
    {
        $wallet = $this->walletService->rejectManualTopUp(new RejectManualTopUpDto(
            (int) $this->request->get('wallet_id'),
            'rejected',
            (string) $this->request->get('reason')
        ));

        return $this->response->success([
            'wallet' => $wallet,
            'message' => 'Manual top up rejected successfully'
        ]);
    }

    public function myPendingWalletTransactions()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletService
            ->filterBy('user_id', $user_id)
            ->filterBy('status', 'pending')
            ->filterBy('type', 'online')
            ->filter($this->request->all())->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Pending wallet transactions fetched successfully'
        ]);
    }

    public function myApprovedWalletTransactions()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $user_id = $user->id;
        $wallets = $this->walletService
            ->filterBy('user_id', $user_id)
            ->filterBy('status', 'approved')
            ->filterBy('type', 'online')
            ->filter($this->request->all())->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Approved wallet transactions fetched successfully'
        ]);
    }

    public function manualPendingWalletTransactions()
    {
        $wallets = $this->walletService
            ->filterBy('status', 'pending')
            ->filterBy('type', 'manual')
            ->filter($this->request->all())->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual pending wallet transactions fetched successfully'
        ]);
    }

    public function manualApprovedWalletTransactions()
    {
        $wallets = $this->walletService
            ->filterBy('status', 'approved')
            ->filterBy('type', 'manual')
            ->filter($this->request->all())->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual approved wallet transactions fetched successfully'
        ]);
    }

    public function manualRejectedWalletTransactions()
    {
        $wallets = $this->walletService
            ->filterBy('status', 'rejected')
            ->filterBy('type', 'manual')
            ->filter($this->request->all())->fetch();
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
