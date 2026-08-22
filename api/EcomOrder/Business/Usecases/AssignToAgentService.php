<?php
namespace EcomOrder\Business\Usecases;

use EcomOrder\Business\Dtos\AssignToAgentDto;
use EcomOrder\Business\Usecases\Mail\SendOrderAssignedToAgentNotificationToCustomerService;
use EcomOrder\Data\EcomOrderRepositoryInterface;
use Shared\Contracts;
use User\Data\UserRepositoryInterface;

class AssignToAgentService
{
    private EcomOrderRepositoryInterface $ecomOrderRepository;
    private UserRepositoryInterface $userRepository;
    private SendOrderAssignedToAgentNotificationToCustomerService $sendOrderAssignedToAgentNotificationToCustomerService;

    public function __construct(
        EcomOrderRepositoryInterface $ecomOrderRepository,
        UserRepositoryInterface $userRepository,
        SendOrderAssignedToAgentNotificationToCustomerService $sendOrderAssignedToAgentNotificationToCustomerService
    ) {
        $this->ecomOrderRepository = $ecomOrderRepository;
        $this->userRepository = $userRepository;
        $this->sendOrderAssignedToAgentNotificationToCustomerService = $sendOrderAssignedToAgentNotificationToCustomerService;
    }

    public function execute(AssignToAgentDto $assignToAgentDto)
    {
        $order_id = $assignToAgentDto->order_id;
        $agent_id = $assignToAgentDto->agent_id;

        Contracts::requires($order_id > 0, 'Order ID is required');
        Contracts::requires($agent_id > 0, 'Agent ID is required');

        $agent = $this->userRepository->find($agent_id);
        Contracts::requireEntityFound($agent, 'Agent');
        Contracts::requires($agent->role === 'agent', $agent->name . ' is not an agent');

        $order = $this->ecomOrderRepository->find($order_id);
        Contracts::requireEntityFound($order, 'Order');

        $order->agent_id = $agent_id;
        $order = $this->ecomOrderRepository->save($order);

        $this->sendOrderAssignedToAgentNotificationToCustomerService->execute($order_id, $agent_id);
        return $order;
    }
}
