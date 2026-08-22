<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class PendingPaymentsForUserService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->userRepository = $userRepository;
    }

    public function query(int $user_id)
    {
        Contracts::requires($user_id > 0, 'User ID is required');
        $user = $this->userRepository->find($user_id);
        Contracts::requireEntityFound($user, 'User');

        return $this->ecomOrderRepository->query([
            'user_id' => $user_id,
            'payment_status' => 'pending',
            'payable_types' => 1,
        ])->fetchAll();
    }
}
