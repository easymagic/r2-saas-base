<?php
namespace SnappyOrder\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class GetMyOrdersAsCustomerService
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

    public function query(int $customer_id, array $filters = [])
    {
        $customer = $this->userRepository->find($customer_id);
        Contracts::requireEntityFound($customer, 'Customer');

        $filters['user_id'] = $customer_id;
        return $this->snappyOrderRepository->query($filters);
    }
}
