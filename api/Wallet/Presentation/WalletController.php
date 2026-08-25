<?php

namespace Wallet\Presentation;

use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Business\Usecases\ApproveManualTopUpService;
use Wallet\Business\Usecases\MigrateService;
use Wallet\Business\Usecases\RejectManualTopUpService;
use Wallet\Business\Usecases\TopUpManualService;
use Wallet\Business\Usecases\TopUpOnlineService;
use Wallet\Data\WalletRepositoryInterface;
use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Json\JsonResponseServiceInterface;

class WalletController
{
    private TopUpOnlineService $topUpOnlineService;
    private TopUpManualService $topUpManualService;
    private ApproveManualTopUpService $approveManualTopUpService;
    private RejectManualTopUpService $rejectManualTopUpService;
    private MigrateService $migrateService;
    private Request $request;
    private JsonResponseServiceInterface $response;
    private ApiCredentialServiceInterface $apiCredentialService;
    private WalletRepositoryInterface $walletRepository;

    public function __construct(
        TopUpOnlineService $topUpOnlineService,
        TopUpManualService $topUpManualService,
        ApproveManualTopUpService $approveManualTopUpService,
        RejectManualTopUpService $rejectManualTopUpService,
        MigrateService $migrateService,
        Request $request,
        JsonResponseServiceInterface $response,
        ApiCredentialServiceInterface $apiCredentialService,
        WalletRepositoryInterface $walletRepository
    ) {
        $this->topUpOnlineService = $topUpOnlineService;
        $this->topUpManualService = $topUpManualService;
        $this->approveManualTopUpService = $approveManualTopUpService;
        $this->rejectManualTopUpService = $rejectManualTopUpService;
        $this->migrateService = $migrateService;
        $this->request = $request;
        $this->response = $response;
        $this->apiCredentialService = $apiCredentialService;
        $this->walletRepository = $walletRepository;
    }

    public function topUpOnline()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $wallet = $this->topUpOnlineService->execute(new TopUpOnlineDto(
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
        $wallet = $this->topUpManualService->execute(new TopUpManualDto(
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
        $wallet = $this->approveManualTopUpService->execute(new ApproveManualTopUpDto(
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
        $wallet = $this->rejectManualTopUpService->execute(new RejectManualTopUpDto(
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
        $filters = array_merge($this->request->all(), [
            'user_id' => $user->id,
            'status' => 'pending',
            'type' => 'online',
        ]);
        $wallets = $this->walletRepository->query($filters)->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Pending wallet transactions fetched successfully'
        ]);
    }

    public function myApprovedWalletTransactions()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $filters = array_merge($this->request->all(), [
            'user_id' => $user->id,
            'status' => 'approved',
            'type' => 'online',
        ]);
        $wallets = $this->walletRepository->query($filters)->fetch();

        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Approved wallet transactions fetched successfully'
        ]);
    }

    public function manualPendingWalletTransactions()
    {
        $filters = array_merge($this->request->all(), [
            'status' => 'pending',
            'type' => 'manual',
        ]);
        $wallets = $this->walletRepository->query($filters)->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual pending wallet transactions fetched successfully'
        ]);
    }

    public function manualApprovedWalletTransactions()
    {
        $filters = array_merge($this->request->all(), [
            'status' => 'approved',
            'type' => 'manual',
        ]);
        $wallets = $this->walletRepository->query($filters)->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual approved wallet transactions fetched successfully'
        ]);
    }

    public function manualRejectedWalletTransactions()
    {
        $filters = array_merge($this->request->all(), [
            'status' => 'rejected',
            'type' => 'manual',
        ]);
        $wallets = $this->walletRepository->query($filters)->fetch();
        return $this->response->success([
            'wallets' => $wallets,
            'message' => 'Manual rejected wallet transactions fetched successfully'
        ]);
    }

    public function migrate()
    {
        $migration = $this->migrateService->execute();
        return $this->response->success([
            'migration' => $migration,
            'message' => 'Wallet migration successful'
        ]);
    }
}
