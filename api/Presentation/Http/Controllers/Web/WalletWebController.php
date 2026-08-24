<?php

namespace Presentation\Http\Controllers\Web;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use Presentation\View\View;
use Presentation\Web\WebSession;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use User\Business\Usecases\GetWalletBalanceService;
use User\Data\UserRepositoryInterface;
use Wallet\Business\Dtos\ApproveManualTopUpDto;
use Wallet\Business\Dtos\RejectManualTopUpDto;
use Wallet\Business\Dtos\TopUpManualDto;
use Wallet\Business\Dtos\TopUpOnlineDto;
use Wallet\Business\Usecases\ApproveManualTopUpService;
use Wallet\Business\Usecases\RejectManualTopUpService;
use Wallet\Business\Usecases\TopUpManualService;
use Wallet\Business\Usecases\TopUpOnlineService;
use Wallet\Data\WalletRepositoryInterface;

class WalletWebController
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    private GetWalletBalanceService $getWalletBalanceService;
    private TopUpManualService $topUpManualService;
    private TopUpOnlineService $topUpOnlineService;
    private ApproveManualTopUpService $approveManualTopUpService;
    private RejectManualTopUpService $rejectManualTopUpService;
    private WalletRepositoryInterface $walletRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ApiCredentialServiceInterface $apiCredentialService,
        Request $request,
        GetWalletBalanceService $getWalletBalanceService,
        TopUpManualService $topUpManualService,
        TopUpOnlineService $topUpOnlineService,
        ApproveManualTopUpService $approveManualTopUpService,
        RejectManualTopUpService $rejectManualTopUpService,
        WalletRepositoryInterface $walletRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->apiCredentialService = $apiCredentialService;
        $this->request = $request;
        $this->getWalletBalanceService = $getWalletBalanceService;
        $this->topUpManualService = $topUpManualService;
        $this->topUpOnlineService = $topUpOnlineService;
        $this->approveManualTopUpService = $approveManualTopUpService;
        $this->rejectManualTopUpService = $rejectManualTopUpService;
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $balance = $this->getWalletBalanceService->query((int) $user->id);
        $pendingOnline = $this->walletRepository->query([
            'user_id' => $user->id,
            'status' => 'pending',
            'type' => 'online',
        ])->fetchAll();
        $approvedOnline = $this->walletRepository->query([
            'user_id' => $user->id,
            'status' => 'approved',
            'type' => 'online',
        ])->fetchAll();
        $manual = $this->walletRepository->query([
            'user_id' => $user->id,
            'type' => 'manual',
        ])->fetchAll();

        View::render('wallet/index', [
            'title' => 'Wallet',
            'subtitle' => 'Balance and top-ups',
            'nav' => 'wallet',
            'user' => $user,
            'balance' => $balance,
            'pending_online' => is_array($pendingOnline) ? $pendingOnline : [],
            'approved_online' => is_array($approvedOnline) ? $approvedOnline : [],
            'manual_transactions' => is_array($manual) ? $manual : [],
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function topUpManual()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $this->topUpManualService->execute(new TopUpManualDto(
                (int) $user->id,
                (float) $this->request->get('amount'),
                uniqid('TOPUP-') . '-' . time(),
                'Top up manual',
                'pending',
                (array) $this->request->get('proof_of_payment_screenshot1', []),
                $this->request->get('proof_of_payment_screenshot2', []),
                $this->request->get('proof_of_payment_screenshot3', [])
            ));
            WebSession::flash('success', 'Top-up request submitted for approval.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/wallet');
    }

    public function topUpOnline()
    {
        $user = $this->apiCredentialService->getAuthUser();
        try {
            $wallet = $this->topUpOnlineService->execute(new TopUpOnlineDto(
                (int) $user->id,
                (float) $this->request->get('amount'),
                uniqid('TOPUP-') . '-' . time(),
                'Top up online',
                'pending'
            ));
            if (!empty($wallet->payment_url)) {
                header('Location: ' . $wallet->payment_url);
                exit;
            }
            WebSession::flash('success', 'Online top-up initiated.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/wallet');
    }

    public function adminTopups()
    {
        $user = $this->apiCredentialService->getAuthUser();
        $tab = (string) $this->request->get('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved', 'rejected'], true)) {
            $tab = 'pending';
        }
        $transactions = $this->walletRepository->query([
            'status' => $tab,
            'type' => 'manual',
        ])->fetchAll();
        $usersById = [];
        if (is_array($transactions)) {
            foreach ($transactions as $tx) {
                if (!isset($usersById[$tx->user_id])) {
                    $usersById[$tx->user_id] = $this->userRepository->find((int) $tx->user_id);
                }
            }
        }

        View::render('wallet/admin-topups', [
            'title' => 'Wallet top-ups',
            'subtitle' => 'Manual top-up approvals',
            'nav' => 'admin-topups',
            'layout_nav' => 'admin',
            'user' => $user,
            'balance' => $this->getWalletBalanceService->query((int) $user->id),
            'tab' => $tab,
            'transactions' => is_array($transactions) ? $transactions : [],
            'users_by_id' => $usersById,
            'flash' => WebSession::pullFlash(),
        ]);
    }

    public function approveTopup()
    {
        $walletId = (int) $this->request->get('wallet_id');
        try {
            $this->approveManualTopUpService->execute(new ApproveManualTopUpDto($walletId, 'approved'));
            WebSession::flash('success', 'Top-up approved.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/wallet/topups');
    }

    public function rejectTopup()
    {
        $walletId = (int) $this->request->get('wallet_id');
        try {
            $this->rejectManualTopUpService->execute(new RejectManualTopUpDto(
                $walletId,
                'rejected',
                (string) $this->request->get('reason', 'Rejected by admin')
            ));
            WebSession::flash('success', 'Top-up rejected.');
        } catch (\Exception $e) {
            WebSession::flash('error', $e->getMessage());
        }
        WebSession::redirect('/admin/wallet/topups');
    }

}
