<?php
namespace SnappyOrder\Business\Usecases;

use Shared\Contracts;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class GetMyOrdersAsAgentService
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

    public function query(int $agent_id, array $filters = [])
    {
        $agent = $this->userRepository->find($agent_id);
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', 'User is not an agent');

        $filters['agent_id'] = $agent_id;
        return $this->snappyOrderRepository->query($filters);
    }
}
