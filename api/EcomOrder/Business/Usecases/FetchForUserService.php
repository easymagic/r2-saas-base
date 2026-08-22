<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class FetchForUserService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private UserRepositoryInterface $userRepository;
    private EcomOrderSupport $ecomOrderSupport;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        UserRepositoryInterface $userRepository,
        EcomOrderSupport $ecomOrderSupport
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->userRepository = $userRepository;
        $this->ecomOrderSupport = $ecomOrderSupport;
    }

    public function query(int $user_id, array $filters = [])
    {
        Contracts::requires($user_id > 0, 'User ID is required');
        $user = $this->userRepository->find($user_id);
        Contracts::requireEntityFound($user, 'User');
        $filters['user_id'] = $user_id;
        return $this->ecomOrderRepository->query($this->ecomOrderSupport->sanitizeListFilters($filters));
    }
}
