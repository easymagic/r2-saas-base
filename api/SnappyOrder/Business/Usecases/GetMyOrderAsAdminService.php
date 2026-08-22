<?php
namespace SnappyOrder\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class GetMyOrderAsAdminService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->userRepository = $userRepository;
    }

    public function query(int $admin_id, array $filters = [])
    {
        $admin = $this->userRepository->find($admin_id);
        Contracts::requireEntityFound($admin, 'Admin');
        Contracts::requires($admin->isAdmin(), 'User is not an admin');

        return $this->snappyOrderRepository->query($filters);
    }
}
