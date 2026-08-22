<?php
namespace EcomOrder\Business\Usecases\Mail;

use Exception;
use R2Packages\Framework\Application\Mail\MailServiceInterface;
use User\Data\UserRepositoryInterface;

class SendOrderAssignedToAgentNotificationToCustomerService
{
    private MailServiceInterface $mailService;
    private EcomOrderMailTemplate $mailTemplate;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        MailServiceInterface $mailService,
        EcomOrderMailTemplate $mailTemplate,
        UserRepositoryInterface $userRepository
    ) {
        $this->mailService = $mailService;
        $this->mailTemplate = $mailTemplate;
        $this->userRepository = $userRepository;
    }

    public function execute(int $order_id, int $agent_id)
    {
        $order = $this->mailTemplate->requireOrder($order_id);
        $agent = $this->userRepository->find($agent_id);
        if ($agent->isEmpty()) {
            throw new Exception('Agent not found');
        }
        $subject = 'Order #' . (int) $order->id . ' assigned to an agent';
        $body = $this->mailTemplate->renderTemplate('order_assigned_agent_customer.html', $this->mailTemplate->orderVars($order, [
            'intro' => 'Your order has been assigned to a delivery agent.',
            'agent_name' => $agent->name,
        ]));
        $this->mailService->send($order->customer_email, $subject, $this->mailTemplate->from(), $body);
        $this->mailTemplate->notifyUser(
            (int) $order->user_id,
            $subject,
            'Order #' . (int) $order->id . ' was assigned to ' . $agent->name . '.'
        );
    }
}
