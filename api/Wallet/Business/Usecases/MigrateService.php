<?php
namespace Wallet\Business\Usecases;

use Wallet\Data\WalletMigrationRepositoryInterface;

class MigrateService
{
    private WalletMigrationRepositoryInterface $walletMigrationRepository;

    public function __construct(WalletMigrationRepositoryInterface $walletMigrationRepository)
    {
        $this->walletMigrationRepository = $walletMigrationRepository;
    }

    public function execute()
    {
        return $this->walletMigrationRepository->migrate();
    }
}
