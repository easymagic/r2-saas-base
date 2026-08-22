<?php
namespace SnappyOrder\Business\Usecases;

use ProxyOrderChangeLog\Business\Dtos\LogDto as ProxyOrderChangeLogDto;
use ProxyOrderChangeLog\Business\Usecases\LogService as ProxyOrderChangeLogService;
use Shared\Contracts;
use SnappyOrder\Business\Dtos\AssignToAgentDto;
use SnappyOrder\Business\Usecases\Mail\NotifyAgentOfOrderAssignmentService;
use SnappyOrder\Business\Usecases\Mail\NotifyCustomerOfAgentAssignmentService;
use SnappyOrder\Data\SnappyOrderRepositoryInterface;
use User\Data\UserRepositoryInterface;

class AssignToAgentService
{
    private SnappyOrderRepositoryInterface $snappyOrderRepository;
    private UserRepositoryInterface $userRepository;
    private ProxyOrderChangeLogService $proxyOrderChangeLogService;
    private NotifyAgentOfOrderAssignmentService $notifyAgentOfOrderAssignmentService;
    private NotifyCustomerOfAgentAssignmentService $notifyCustomerOfAgentAssignmentService;

    public function __construct(
        SnappyOrderRepositoryInterface $snappyOrderRepository,
        UserRepositoryInterface $userRepository,
        ProxyOrderChangeLogService $proxyOrderChangeLogService,
        NotifyAgentOfOrderAssignmentService $notifyAgentOfOrderAssignmentService,
        NotifyCustomerOfAgentAssignmentService $notifyCustomerOfAgentAssignmentService
    ) {
        $this->snappyOrderRepository = $snappyOrderRepository;
        $this->userRepository = $userRepository;
        $this->proxyOrderChangeLogService = $proxyOrderChangeLogService;
        $this->notifyAgentOfOrderAssignmentService = $notifyAgentOfOrderAssignmentService;
        $this->notifyCustomerOfAgentAssignmentService = $notifyCustomerOfAgentAssignmentService;
    }

    public function execute(AssignToAgentDto $assignToAgentDto)
    {
        $agent = $this->userRepository->find($assignToAgentDto->agent_id);
        Contracts::requireEntityFound($agent, 'agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');

        $order = $this->snappyOrderRepository->find($assignToAgentDto->order_id);
        Contracts::requireEntityFound($order, 'order');

        $previousAgentId = $order->agent_id;
        $order->agent_id = $assignToAgentDto->agent_id;
        $order->status = 'placed';
        $order = $this->snappyOrderRepository->save($order);

        $this->proxyOrderChangeLogService->execute(new ProxyOrderChangeLogDto(
            $order->id,
            'agent_id',
            (string) $previousAgentId,
            (string) $assignToAgentDto->agent_id
        ));

        $this->notifyAgentOfOrderAssignmentService->execute($order->id, $assignToAgentDto->agent_id);
        $this->notifyCustomerOfAgentAssignmentService->execute($order->id, $assignToAgentDto->agent_id);

        return $order;
    }
}
