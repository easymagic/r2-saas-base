<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class FetchForAgentService
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

    public function query(int $agent_id, array $filters = [])
    {
        Contracts::requires($agent_id > 0, 'Agent ID is required');
        $agent = $this->userRepository->find($agent_id);
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');
        $filters['agent_id'] = $agent_id;
        return $this->ecomOrderRepository->query($this->ecomOrderSupport->sanitizeListFilters($filters));
    }
}
